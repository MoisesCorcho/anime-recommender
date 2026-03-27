# Implementación de Stripe — Documentación Técnica

Sistema de monetización por créditos para búsquedas semánticas del anime-recommender.

---

## Índice

1. [Contexto y Motivación](#1-contexto-y-motivación)
2. [Decisiones de Diseño](#2-decisiones-de-diseño)
3. [Schema de Base de Datos](#3-schema-de-base-de-datos)
4. [Mapa de Archivos](#4-mapa-de-archivos)
5. [Flujo Completo de Código](#5-flujo-completo-de-código)
6. [Configuración para Desarrollo](#6-configuración-para-desarrollo)
7. [Configuración del Webhook en Stripe Dashboard](#7-configuración-del-webhook-en-stripe-dashboard)
8. [Comandos de Test con Stripe CLI](#8-comandos-de-test-con-stripe-cli)
9. [Flujo desde Registro hasta Compra](#9-flujo-desde-registro-hasta-compra)

---

## 1. Contexto y Motivación

Cada búsqueda semántica en el sistema tiene un costo real:
- Llamada a la API de OpenAI (`text-embedding-3-small`) para vectorizar la query del usuario.
- Operación de similitud coseno en PostgreSQL con pgvector.

El modelo de monetización cubre estos costos operativos mediante un sistema de **créditos**:

| Tier | Créditos | Mecanismo |
|------|----------|-----------|
| Free | 50 créditos al registro | Regalo único, no se renueva |
| Pro | 5,000 créditos/mes | Suscripción mensual vía Stripe |
| Pack One-Off | 100 / 500 / 1,000 | Compra única vía Stripe |

**Regla de consumo**: 1 búsqueda semántica exitosa = 1 crédito debitado. Si la búsqueda falla (OpenAI no responde), el crédito **no** se descuenta.

---

## 2. Decisiones de Diseño

### 2.1 ¿Por qué Laravel Cashier y no el SDK de Stripe directamente?

Cashier provee de forma nativa:
- El trait `Billable` en el modelo `User` para manejar customers de Stripe.
- Sincronización automática de la tabla `subscriptions` cuando llegan webhooks.
- Helpers para crear checkout sessions (`Checkout::create()`).
- Manejo de la verificación de firma del webhook en su propio controller.

Usar el SDK directamente hubiera requerido implementar todo eso manualmente.

### 2.2 ¿Por qué `PaymentGatewayInterface` si ya tenemos Cashier?

Es el **Principio de Inversión de Dependencias (DIP)** de SOLID. `CreditCheckoutService` no importa ninguna clase de Stripe ni de Cashier — depende de la interface. El único lugar que conoce a Stripe es `StripePaymentGateway`.

Consecuencia práctica: si mañana se cambia de Stripe a Braintree, se crea `BraintreePaymentGateway implements PaymentGatewayInterface` y se cambia **una línea** en `AppServiceProvider`. El resto del sistema no se toca.

```php
// AppServiceProvider::register()
$this->app->bind(
    PaymentGatewayInterface::class,
    StripePaymentGateway::class, // ← solo esta línea cambia
);
```

### 2.3 ¿Por qué la ruta de Cashier y no una ruta custom?

El primer diseño tenía una ruta propia `/webhooks/stripe` apuntando a un `StripeWebhookController`. Esto rompía Cashier porque interceptábamos todos los webhooks antes de que Cashier pudiera procesarlos. La tabla `subscriptions` nunca se sincronizaba.

La solución: usar **exclusivamente** la ruta de Cashier `/stripe/webhook` y escuchar el evento `WebhookReceived` que Cashier dispara internamente una vez que verificó la firma y procesó sus propias tablas.

```
❌ Antes:  POST /webhooks/stripe → StripeWebhookController (interceptábamos todo)
✅ Ahora:  POST /stripe/webhook  → Cashier WebhookController → Event(WebhookReceived)
                                                               → StripeEventListener (nosotros)
```

Cashier hace su trabajo primero (verifica firma, sincroniza subscriptions). Nosotros escuchamos el evento que él mismo dispara y ejecutamos la lógica de créditos sin pisarle la manguera.

### 2.4 ¿Por qué `DB::transaction()` con `lockForUpdate()` en la deducción?

**Race condition**: un usuario con 1 crédito abre dos tabs y lanza dos búsquedas simultáneas. Sin locking, ambas pasan el check `if (balance >= 1)`, ambas debitan, y el balance queda en -1.

El lock pesimista (`SELECT ... FOR UPDATE`) serializa el acceso: la segunda transacción espera a que la primera termine antes de leer el balance.

```php
// CreditService::deductForSemanticSearch()
$this->checkBalance->execute($user);       // pre-check sin lock (falla rápido si es obvio)

return DB::transaction(function () use ($user) {
    $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id); // lock
    $this->checkBalance->execute($lockedUser); // re-check con el dato real y bloqueado
    return $this->deduct->execute($lockedUser);
});
```

### 2.5 ¿Por qué OpenAI se llama ANTES de la transacción DB?

Una transacción abierta mantiene una conexión de base de datos ocupada. Si se llamara a OpenAI dentro de la transacción, esa conexión quedaría bloqueada durante 2 a 10 segundos de I/O HTTP. Bajo carga concurrente, el connection pool se agota.

El orden correcto es:
1. Obtener el embedding de OpenAI (sin transacción abierta).
2. Si OpenAI falla → no se abre ninguna transacción → no se debita nada.
3. Si OpenAI responde → abrir transacción → lock → debitar → commit.

### 2.6 ¿Por qué la tabla `stripe_webhook_events` y la idempotencia manual?

Stripe garantiza entrega **at least once**: el mismo webhook puede llegar 2 o más veces si el endpoint tarda en responder o hay un reintento automático. Sin idempotencia, un pago podría acreditar créditos dos veces.

La solución es intentar insertar el `stripe_event_id` como registro único antes de procesar. Si ya existía, el evento fue procesado antes y se ignora silenciosamente.

```php
$event = StripeWebhookEvent::firstOrCreate(
    ['stripe_event_id' => $dto->eventId],
    [...datos del evento...]
);

if (! $event->wasRecentlyCreated) {
    return; // duplicado — ignorar
}
```

> **Por qué `firstOrCreate` y no `insertOrIgnore`**: `insertOrIgnore` bypasea el modelo Eloquent, y el trait `HasUlids` nunca dispara. La PK queda en `null` y falla con un constraint error. `firstOrCreate` pasa por el modelo y genera el ULID correctamente.

### 2.7 ¿Por qué `credit_balance` en `users` Y también la tabla `credit_transactions`?

Dos necesidades distintas:

- **`credit_balance`**: necesario para verificar el saldo en cada búsqueda sin recalcular. Calcular `SUM` del ledger en cada request es O(n) e inaceptable.
- **`credit_transactions`**: ledger inmutable para auditoría, soporte, y detección de inconsistencias. Sin él, no hay forma de saber qué pasó si el balance es incorrecto.

El campo `balance_after` en cada transacción actúa como snapshot: permite reconstruir el historial completo y detectar divergencias.

**Regla de oro**: `credit_balance` en `users` **nunca** se modifica directamente. El único punto de mutación es `CreditService`. Esto garantiza que el campo siempre esté en sintonía con el ledger.

---

## 3. Schema de Base de Datos

### Columnas nuevas en `users`

```sql
credit_balance       INTEGER     NOT NULL DEFAULT 0
subscription_tier    VARCHAR     NOT NULL DEFAULT 'FREE'
subscription_ends_at TIMESTAMP   NULL
stripe_id            VARCHAR     NULL       -- Cashier (Billable trait)
pm_type              VARCHAR     NULL       -- Cashier
pm_last_four         VARCHAR(4)  NULL       -- Cashier
trial_ends_at        TIMESTAMP   NULL       -- Cashier
```

### Tabla `credit_transactions` (ledger inmutable)

```sql
id             VARCHAR(26)    PRIMARY KEY              -- ULID
user_id        BIGINT         NOT NULL FK → users.id   CASCADE DELETE
type           VARCHAR        NOT NULL                 -- 'DEBIT' | 'CREDIT'
amount         INTEGER        NOT NULL CHECK(amount > 0)
reason         VARCHAR        NOT NULL                 -- ver CreditTransactionReason enum
reference_id   VARCHAR        NULL                     -- stripe payment_intent_id o interaction ULID
balance_after  INTEGER        NOT NULL                 -- snapshot post-operación
created_at     TIMESTAMP      NOT NULL
-- Sin updated_at: inmutable por diseño
```

### Tabla `stripe_webhook_events` (idempotencia)

```sql
id              VARCHAR(26)    PRIMARY KEY
stripe_event_id VARCHAR        NOT NULL UNIQUE          -- "evt_xxxxx" — clave de idempotencia
event_type      VARCHAR        NOT NULL                 -- "checkout.session.completed"
payload         JSON           NOT NULL                 -- evento completo para auditoría
processed_at    TIMESTAMP      NULL                     -- NULL = procesando | timestamp = completo
created_at      TIMESTAMP      NOT NULL
-- Sin updated_at: inmutable por diseño
```

### Tablas de Cashier (gestionadas por el package)

Cashier crea automáticamente `subscriptions` y `subscription_items` al correr `artisan cashier:install`.

---

## 4. Mapa de Archivos

```
app/
├── Actions/Credits/
│   ├── CheckCreditBalanceAction.php      Verifica balance >= 1. Lanza InsufficientCreditsException.
│   ├── DeductCreditAction.php            Debita 1 crédito. SOLO dentro de DB::transaction() + lockForUpdate.
│   ├── TopUpCreditsAction.php            Acredita N créditos. Crea entrada en credit_transactions.
│   └── InitializeUserCreditsAction.php   Lee config('credits.registration_bonus') y acredita.
│
├── DataTransferObjects/
│   ├── Credits/
│   │   ├── CreditDeductionResultDTO.php   balanceAfter + CreditTransaction resultante
│   │   └── CreditTopUpDTO.php             amount + CreditTransactionReason + referenceId opcional
│   └── Payments/
│       ├── CheckoutSessionDTO.php         sessionId + checkoutUrl
│       ├── StripeWebhookPayloadDTO.php    eventId + eventType + data (array)
│       └── SubscriptionDataDTO.php        subscriptionId + status + currentPeriodEnd
│
├── Enums/
│   ├── CreditTransactionType.php     Debit='DEBIT' | Credit='CREDIT'
│   ├── CreditTransactionReason.php   RegistrationBonus | SemanticSearch | SubscriptionRenewal | PackPurchase
│   └── SubscriptionTier.php          Free='FREE' | Pro='PRO'
│
├── Exceptions/
│   ├── InsufficientCreditsException.php    Lanzada cuando balance < 1
│   └── InvalidStripeWebhookException.php   Lanzada si la firma del webhook es inválida
│
├── Jobs/
│   └── InitializeUserCreditsJob.php   ShouldQueue. Llama CreditService::initializeRegistrationBonus().
│
├── Listeners/
│   ├── GrantRegistrationCreditsListener.php  Escucha Registered → dispatch InitializeUserCreditsJob
│   └── StripeEventListener.php               Escucha WebhookReceived → filtra 3 eventos → CreditCheckoutService
│
├── Models/
│   ├── User.php               Tiene trait Billable, cast SubscriptionTier, relación creditTransactions()
│   ├── CreditTransaction.php  HasUlids, $timestamps=false, casts de Enums
│   └── StripeWebhookEvent.php HasUlids, $timestamps=false
│
├── Services/
│   ├── CreditService.php              Único punto de mutación de credit_balance
│   ├── CreditCheckoutService.php      Orquesta checkout y procesamiento de webhooks
│   └── Payments/
│       ├── PaymentGatewayInterface.php   Contrato DIP: createCheckoutSession, getActiveSubscription, cancelSubscription
│       └── StripePaymentGateway.php      ÚNICA clase que importa Cashier/Stripe. Implementa la interface.
│
└── Http/Controllers/
    └── CheckoutController.php   POST /checkout/{plan} → redirect a Stripe Hosted Checkout

routes/web.php
    POST /checkout/{plan}   → CheckoutController          (middleware: auth, verified)
    POST /stripe/webhook    → Cashier WebhookController   (sin auth, sin CSRF — protegido por firma Stripe)

config/credits.php
    registration_bonus      50      (env CREDITS_REGISTRATION_BONUS)
    pro_monthly_allowance   5000    (env CREDITS_PRO_MONTHLY_ALLOWANCE)
    rate_limit_free         5/min   (env CREDITS_RATE_LIMIT_FREE)
    rate_limit_pro          10/min  (env CREDITS_RATE_LIMIT_PRO)
```

---

## 5. Flujo Completo de Código

### 5.1 Flujo de Registro (créditos de cortesía)

```
POST /register
  │
  ▼
Breeze registra al usuario (credit_balance = 0 por defecto)
  │
  ▼
Illuminate\Auth\Events\Registered disparado internamente por Laravel
  │
  ▼
AppServiceProvider::boot()
  Event::listen(Registered::class, GrantRegistrationCreditsListener::class)
  │
  ▼
GrantRegistrationCreditsListener::handle(Registered $event)
  → InitializeUserCreditsJob::dispatch($event->user)    ← job enviado a la cola
  │
  ▼ (procesado por el worker)
InitializeUserCreditsJob::handle(CreditService $credits)
  → $credits->initializeRegistrationBonus($this->user)
  │
  ▼
CreditService::initializeRegistrationBonus()
  → InitializeUserCreditsAction::execute($user)
      → $amount = config('credits.registration_bonus')  // 50
      → $user->increment('credit_balance', $amount)
      → CreditTransaction::create(type=CREDIT, reason=REGISTRATION_BONUS, amount=50, balance_after=50)
```

### 5.2 Flujo de Checkout (compra de plan o pack)

```
POST /checkout/{plan}     (plan = 'pro' | 'pack_100' | 'pack_500' | 'pack_1000')
  │
  ▼
CheckoutController::create(Request $request, string $plan)
  → Valida que $plan sea uno de los planes permitidos (abort 422 si no)
  → $checkout->createCheckoutSession($user, $plan)
  │
  ▼
CreditCheckoutService::createCheckoutSession(User $user, string $plan)
  → Resuelve $priceId desde config('credits.prices.{$plan}')
  → $this->gateway->createCheckoutSession($user, $priceId, $successUrl, $cancelUrl)
  │
  ▼
StripePaymentGateway::createCheckoutSession()    ← única clase que toca Cashier
  → Cashier\Checkout::create($user, ['mode' => 'subscription', 'line_items' => [...]])
  → Retorna CheckoutSessionDTO(sessionId, checkoutUrl)
  │
  ▼
CheckoutController → redirect()->away($dto->checkoutUrl)
  │
  ▼
Usuario en Stripe Hosted Checkout
  → Ingresa tarjeta y paga
  → Stripe redirige a /checkout/success
  → Stripe dispara webhook checkout.session.completed → POST /stripe/webhook
```

### 5.3 Flujo del Webhook (procesamiento de evento de pago)

```
POST /stripe/webhook
  │
  ▼
Laravel\Cashier\Http\Controllers\WebhookController::handleWebhook()
  → Verifica firma HMAC con STRIPE_WEBHOOK_SECRET   (HTTP 400 si inválida)
  → Procesa eventos de suscripción propios de Cashier (actualiza tabla subscriptions)
  → Dispara internamente: event(new WebhookReceived($payload))
  │
  ▼
AppServiceProvider: Event::listen(WebhookReceived::class, StripeEventListener::class)
  │
  ▼
StripeEventListener::handle(WebhookReceived $event)
  → ¿$event->payload['type'] está en ['checkout.session.completed', 'invoice.paid', 'customer.subscription.deleted']?
  → NO  → return (Cashier ya lo procesó, nosotros no lo necesitamos)
  → SÍ  → construir StripeWebhookPayloadDTO(eventId, eventType, data)
         → $this->checkout->processWebhookEvent($dto)
  │
  ▼
CreditCheckoutService::processWebhookEvent(StripeWebhookPayloadDTO $dto)
  │
  ├─ IDEMPOTENCIA:
  │    StripeWebhookEvent::firstOrCreate(['stripe_event_id' => $dto->eventId], [...])
  │    Si wasRecentlyCreated === false → return (webhook duplicado, ya fue procesado)
  │
  ├─ match($dto->eventType):
  │    'checkout.session.completed'    → handleCheckoutCompleted($dto)
  │    'invoice.paid'                  → handleInvoicePaid($dto)
  │    'customer.subscription.deleted' → handleSubscriptionCanceled($dto)
  │
  └─ $event->update(['processed_at' => now()])

  ─── handleCheckoutCompleted ───────────────────────────────────────────────
  → Extrae $customerId = $dto->data['object']['customer']
  → $user = User::where('stripe_id', $customerId)->first()  (null → return)
  → Si mode === 'subscription':
      $user->subscription_tier = SubscriptionTier::Pro
      $user->save()
      $credits->topUp($user, CreditTopUpDTO(5000, SubscriptionRenewal, paymentIntentId))
  → Si mode === 'payment' (pack one-off):
      $credits->topUp($user, CreditTopUpDTO(N, PackPurchase, paymentIntentId))
      donde N = config('credits.pack_credits.{$planKey}') (100 | 500 | 1000)

  ─── handleInvoicePaid (renovación mensual automática) ────────────────────
  → Extrae $customerId = $dto->data['object']['customer']
  → $user = User::where('stripe_id', $customerId)->first()
  → $credits->topUp($user, CreditTopUpDTO(5000, SubscriptionRenewal, invoiceId))

  ─── handleSubscriptionCanceled ───────────────────────────────────────────
  → Extrae $customerId = $dto->data['object']['customer']
  → $user = User::where('stripe_id', $customerId)->first()
  → $user->subscription_tier = SubscriptionTier::Free
  → $user->subscription_ends_at = null
  → $user->save()
  (los créditos residuales se conservan — decisión de negocio)
```

### 5.4 Flujo de Búsqueda Semántica (consumo de crédito)

> Este flujo se implementa cuando el componente Livewire de búsqueda esté listo. El `CreditService` ya está preparado para recibirlo.

```
(Livewire component / Controller de búsqueda)
  │
  ▼
RateLimiter::for('semantic-search')   → HTTP 429 si excede 5/min (Free) o 10/min (Pro)
  │
  ▼
CheckCreditBalanceAction::execute($user)
  → balance < 1 → InsufficientCreditsException → mostrar modal de compra
  │
  ▼
OpenAI::generateEmbedding($query)       ← FUERA de la transacción DB (crítico)
  → falla → throw Exception            ← crédito NO debitado
  │
  ▼
CreditService::deductForSemanticSearch($user)
  → Pre-check sin lock (falla rápido en casos obvios)
  → DB::transaction() {
      User::query()->lockForUpdate()->findOrFail($user->id)   ← serializa acceso
      CheckCreditBalanceAction::execute($lockedUser)          ← re-check real
      DeductCreditAction::execute($lockedUser)
        → credit_balance -= 1
        → CreditTransaction::create(DEBIT, SEMANTIC_SEARCH, balance_after)
        → return CreditDeductionResultDTO
    }
  │
  ▼
pgvector::similaritySearch($embedding)
  │
  ▼
LogUserInteractionJob::dispatch()
  │
  ▼
return results
```

---

## 6. Configuración para Desarrollo

### 6.1 Variables de entorno requeridas en `.env`

```env
# Claves de Stripe — modo TEST (nunca producción en dev)
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...   # Ver sección 7 o usar el de stripe listen

# Price IDs — se crean en Stripe Dashboard modo test
STRIPE_PRICE_PRO_MONTHLY=price_...
STRIPE_PRICE_PACK_100=price_...
STRIPE_PRICE_PACK_500=price_...
STRIPE_PRICE_PACK_1000=price_...

# Créditos (opcionales — tienen defaults)
CREDITS_REGISTRATION_BONUS=50
CREDITS_PRO_MONTHLY_ALLOWANCE=5000
CREDITS_RATE_LIMIT_FREE=5
CREDITS_RATE_LIMIT_PRO=10
```

Las claves `pk_test_` y `sk_test_` se obtienen en:
[https://dashboard.stripe.com/test/apikeys](https://dashboard.stripe.com/test/apikeys)

### 6.2 Instalación y migraciones

```bash
# Instalar Cashier (--with-all-dependencies resuelve conflicto con symfony/console en Laravel 13)
vendor/bin/sail composer require laravel/cashier --with-all-dependencies

# Publicar y ejecutar migraciones de Cashier + las propias del sistema de créditos
vendor/bin/sail artisan cashier:install --no-interaction
vendor/bin/sail artisan migrate --no-interaction
```

### 6.3 Worker de colas

El job `InitializeUserCreditsJob` (créditos de registro) se encola. Sin worker, no se ejecuta.

```bash
vendor/bin/sail artisan queue:work
```

Con `QUEUE_CONNECTION=sync` en `.env`, los jobs se ejecutan sincrónicamente y el worker no es necesario en desarrollo.

### 6.4 Price IDs en config/credits.php

Los Price IDs se configuran vía variables de entorno, siguiendo la regla de Laravel: `env()` solo en archivos de configuración, nunca en código de aplicación.

**`config/credits.php`** expone dos secciones nuevas:

```php
'prices' => [
    'pro'       => env('STRIPE_PRICE_PRO_MONTHLY', ''),
    'pack_100'  => env('STRIPE_PRICE_PACK_100', ''),
    'pack_500'  => env('STRIPE_PRICE_PACK_500', ''),
    'pack_1000' => env('STRIPE_PRICE_PACK_1000', ''),
],

'pack_credits' => [
    'pack_100'  => 100,
    'pack_500'  => 500,
    'pack_1000' => 1000,
],
```

**`.env`** (agregar con los IDs reales del Stripe Dashboard en modo test):

```dotenv
STRIPE_PRICE_PRO_MONTHLY=price_xxxx
STRIPE_PRICE_PACK_100=price_xxxx
STRIPE_PRICE_PACK_500=price_xxxx
STRIPE_PRICE_PACK_1000=price_xxxx
```

`CreditCheckoutService` resuelve el Price ID con `config("credits.prices.{$plan}")` y, en el handler de `checkout.session.completed`, hace un reverse lookup sobre `config('credits.prices')` para encontrar el plan key a partir del Price ID recibido en el webhook y obtener los créditos correspondientes desde `config("credits.pack_credits.{$planKey}")`.

---

## 7. Configuración del Webhook en Stripe Dashboard

### 7.1 URL del webhook

```
https://zena-intervocalic-randal.ngrok-free.dev/stripe/webhook
```

Esta es la URL pública estática de ngrok que forwardea a `http://localhost/stripe/webhook`. Al ser un dominio estático de ngrok, no cambia entre reinicios — el webhook en Stripe se configura una sola vez.

### 7.2 Pasos en el Dashboard

1. Ir a [https://dashboard.stripe.com/test/webhooks](https://dashboard.stripe.com/test/webhooks)
2. Click en **"Add endpoint"**
3. **"Endpoint URL"**: `https://zena-intervocalic-randal.ngrok-free.dev/stripe/webhook`
4. En **"Select events to listen to"** agregar estos 3 eventos:

| Evento | Cuándo ocurre |
|--------|--------------|
| `checkout.session.completed` | Usuario completa el pago en Stripe Hosted Checkout |
| `invoice.paid` | Renovación mensual automática de suscripción Pro |
| `customer.subscription.deleted` | Suscripción cancelada (manual o por falta de pago) |

5. Click en **"Add endpoint"**
6. En la página del endpoint, sección **"Signing secret"** → **"Reveal"**
7. Copiar `whsec_...` → pegarlo en `.env` como `STRIPE_WEBHOOK_SECRET`

### 7.3 Levantar ngrok

```bash
ngrok http --domain=zena-intervocalic-randal.ngrok-free.dev 80
```

Ngrok forwardeará el dominio estático → `http://localhost:80`.

---

## 8. Comandos de Test con Stripe CLI

Stripe CLI permite testear webhooks localmente sin necesitar ngrok ni el Dashboard. Es la herramienta correcta para desarrollo rápido.

### 8.1 Instalación

```bash
# macOS
brew install stripe/stripe-cli/stripe

# Linux — descargar desde https://github.com/stripe/stripe-cli/releases
```

### 8.2 Autenticación

```bash
stripe login
```

### 8.3 Escuchar y forwardear webhooks a local

```bash
stripe listen --forward-to http://localhost/stripe/webhook

# Stripe CLI imprime el webhook secret para desarrollo:
# > Ready! Your webhook signing secret is whsec_test_xxxxx
# → Usar ese valor en .env como STRIPE_WEBHOOK_SECRET
```

> Con `stripe listen`, el `STRIPE_WEBHOOK_SECRET` en `.env` debe ser el que imprime la CLI (empieza con `whsec_test_`), **no** el del Dashboard. Son distintos.

### 8.4 Disparar eventos de prueba

Con `stripe listen` corriendo en una terminal, abrir otra y ejecutar:

```bash
# Simular pago completado de suscripción Pro
stripe trigger checkout.session.completed

# Simular renovación mensual automática
stripe trigger invoice.paid

# Simular cancelación de suscripción
stripe trigger customer.subscription.deleted

# Simular creación de suscripción
stripe trigger customer.subscription.created

# Ver todos los eventos disponibles
stripe trigger --help
```

### 8.5 Obtener URL de checkout sin frontend

```bash
vendor/bin/sail artisan tinker --execute "
    \$user = App\Models\User::first();
    \$service = app(App\Services\CreditCheckoutService::class);
    \$dto = \$service->createCheckoutSession(\$user, 'pro');
    echo \$dto->checkoutUrl . PHP_EOL;
"
```

Abrir la URL en el browser, completar el pago con la tarjeta de test, y el webhook llegará automáticamente al endpoint local.

### 8.6 Tarjetas de test de Stripe

| Número | Comportamiento |
|--------|---------------|
| `4242 4242 4242 4242` | Pago exitoso |
| `4000 0000 0000 0002` | Siempre rechazada |
| `4000 0025 0000 3155` | Requiere 3D Secure |
| `4000 0000 0000 9995` | Fondos insuficientes |

Fecha: cualquier fecha futura. CVC: cualquier número de 3 dígitos.

### 8.7 Verificar balance y transacciones desde Tinker

```bash
vendor/bin/sail artisan tinker --execute "
    \$user = App\Models\User::first();
    echo 'Balance: ' . \$user->credit_balance . PHP_EOL;
    echo 'Tier: '    . \$user->subscription_tier->value . PHP_EOL;
    \$user->creditTransactions()->latest('created_at')->take(5)->get()
        ->each(fn(\$t) => print(\$t->type . ' | ' . \$t->amount . ' créditos | balance → ' . \$t->balance_after . PHP_EOL));
"
```

---

## 9. Flujo desde Registro hasta Compra

Recorrido completo de un usuario nuevo:

```
1. REGISTRO
   POST /register
   → Usuario creado con credit_balance = 0
   → Event(Registered) disparado
   → GrantRegistrationCreditsListener → InitializeUserCreditsJob despachado a la cola
   → (worker) Job ejecuta: credit_balance = 50
   → CreditTransaction: CREDIT, REGISTRATION_BONUS, 50, balance_after=50

2. USO GRATUITO (50 búsquedas)
   → Cada búsqueda semántica exitosa: credit_balance -= 1
   → CreditTransaction: DEBIT, SEMANTIC_SEARCH, 1, balance_after=N

3. SALDO AGOTADO
   → balance = 0
   → Próxima búsqueda: InsufficientCreditsException
   → Frontend muestra modal de upgrade (a implementar en Livewire)

4. COMPRA DE PLAN PRO
   → POST /checkout/pro
   → CheckoutController → CreditCheckoutService → StripePaymentGateway
   → redirect a Stripe Hosted Checkout
   → Usuario paga con tarjeta
   → Stripe redirige a /checkout/success
   → Stripe dispara: POST /stripe/webhook con evento checkout.session.completed

5. WEBHOOK PROCESADO
   → Cashier verifica firma, actualiza tabla subscriptions
   → event(WebhookReceived) disparado por Cashier
   → StripeEventListener captura el evento
   → CreditCheckoutService::processWebhookEvent()
       → StripeWebhookEvent creado (idempotencia: evt_xxxxx)
       → handleCheckoutCompleted():
           user.subscription_tier = Pro
           credit_balance += 5000
           CreditTransaction: CREDIT, SUBSCRIPTION_RENEWAL, 5000, balance_after=5000

6. RENOVACIÓN MENSUAL (automática, sin acción del usuario)
   → Stripe cobra automáticamente al mes siguiente
   → Webhook: invoice.paid
   → handleInvoicePaid():
       credit_balance += 5000
       CreditTransaction: CREDIT, SUBSCRIPTION_RENEWAL, 5000

7. CANCELACIÓN
   → Webhook: customer.subscription.deleted (Stripe lo genera automáticamente)
   → handleSubscriptionCanceled():
       user.subscription_tier = Free
       user.subscription_ends_at = null
       (créditos existentes se conservan)
```

---

## Rate Limiting

Configurado en `AppServiceProvider::boot()` y listo para aplicar en la ruta de búsqueda semántica:

```php
// AppServiceProvider
RateLimiter::for('semantic-search', function (Request $request) {
    $user = $request->user();
    $limit = $user?->subscription_tier === SubscriptionTier::Pro
        ? config('credits.rate_limit_pro', 10)   // 10 búsquedas/min
        : config('credits.rate_limit_free', 5);  // 5 búsquedas/min

    return Limit::perMinute($limit)->by($user?->id ?? $request->ip());
});

// Aplicar en la ruta de búsqueda:
Route::middleware(['auth', 'throttle:semantic-search'])->group(function () {
    Route::post('/search', [SearchController::class, 'semantic']);
});
```

---

## Tests

```bash
# Solo tests del sistema de pagos y créditos
vendor/bin/sail artisan test --compact --filter="Stripe|Credits|Checkout|Registration"

# Suite completa
vendor/bin/sail artisan test --compact
```

Los tests de webhook usan el evento `WebhookReceived` directamente — no dependen de Stripe. Los tests de créditos usan factories con `RefreshDatabase`.
