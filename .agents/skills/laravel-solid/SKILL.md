---
name: laravel-solid
description: Enforces SOLID coding standards for this Laravel project. Use when generating, editing, or reviewing any PHP class — controllers, services, models, DTOs, form requests, jobs, or gateways. Prohibits hexagonal/DDD over-engineering. Keeps Laravel-native structure, clean and strictly typed.
---

# Laravel SOLID Coding Standard

This project uses **Laravel native structure**. No Hexagonal Architecture. No DDD. No over-engineering.
The architecture is Laravel + SOLID principles, applied with discipline and pragmatism.

> **These directives are non-negotiable. Every generated PHP file must comply.**

---

## 1. Thin Controllers and Services (SRP — Single Responsibility)

Controllers have **one job**: receive the HTTP request, delegate, and return a response.

**Forbidden in controllers:**
- Business logic of any kind
- Direct Eloquent queries (except trivial lookups via Repository/Service)
- Conditional branching based on domain rules
- Direct instantiation of services (`new MyService()`)

**Required in controllers:**
- Inject Service **interfaces** via constructor (never concrete classes)
- Use `FormRequest` classes for validation
- Map validated data to a DTO via `fromArray()`
- Pass the DTO to the service and return the result

---

## 2. Interfaces and Gateways (OCP, ISP, DIP)

### Service Interfaces (ISP + DIP)
Every service must implement a focused, single-purpose interface.

- Interfaces live in `app/Contracts/Services/`
- Implementations live in `app/Services/`
- Bindings are registered in a `ServiceProvider`
- Controllers and Jobs **must** type-hint the interface, never the concrete class

**Forbidden:**
```php
// ❌ Never inject the concrete class
public function __construct(AnimeRecommendationService $service) {}
```

**Required:**
```php
// ✅ Always inject the interface
public function __construct(AnimeRecommendationServiceContract $service) {}
```

### Gateway Pattern (OCP + DIP)
All integrations with external APIs or third-party libraries must be wrapped in a Gateway.

- Interfaces live in `app/Contracts/Gateways/`
- Implementations live in `app/Gateways/`
- Services depend on the Gateway interface, never on the SDK/library directly

This ensures external dependencies can be swapped or mocked without touching domain logic.

---

## 3. Mandatory Immutable DTOs

**Passing associative arrays between layers is forbidden.**

All data transfer between layers uses DTOs.

### Rules
- Location: `app/DataTransferObjects/`
- Declaration: `final readonly class`
- Constructor: typed properties only, no defaults unless truly optional
- Required methods:
  - `fromArray(array $data): static` — hydration from validated request data
  - `toArray(): array` — serialization for responses, jobs, or logs

### Forbidden
```php
// ❌ Never pass raw arrays between layers
$this->service->recommend(['user_id' => 1, 'genre' => 'action']);
```

### Required
```php
// ✅ Always use a DTO
$dto = RecommendationRequestDto::fromArray($request->validated());
$this->service->recommend($dto);
```

---

## 4. Anemic Models and Isolated Validation

### Eloquent Models
Models are **persistence objects**, nothing more.

**Allowed in models:**
- `$fillable`, `$casts`, `$hidden`
- Relationship methods (`hasMany`, `belongsTo`, etc.)
- Query scopes (`scopeActive`, `scopeByGenre`, etc.)
- Vector/custom scopes for pgvector queries

**Forbidden in models:**
- Business logic methods
- Validation logic
- Calls to services or external APIs
- Static factory methods that encode domain rules

### Validation
All input validation lives exclusively in `FormRequest` classes under `app/Http/Requests/`.
Controllers never call `$request->validate()` inline.

---

## 5. Strict Typing and Mandatory PHPDocs

### Strict Types
Every PHP file must begin with:
```php
<?php

declare(strict_types=1);
```
No exceptions.

### Type Hinting
- All method parameters must be typed
- All return types must be declared (including `void`, `never`, `mixed` when truly needed)
- No untyped properties in DTOs, Services, or Gateways

### PHPDoc Rules
PHPDoc blocks are **required** when they add context that types alone cannot express:

| Case | Required |
|------|----------|
| Generic collections / typed arrays | `@param array<int, AnimeDto>` |
| Exceptions that can propagate | `@throws ServiceException` |
| Business-level description on a class | Class-level `/** */` block |
| Non-obvious method behavior | Method-level `/** */` block |

PHPDoc blocks are **forbidden** when they only restate what the type signature already says (e.g., `@param string $name` on a `string $name` parameter with no extra context).

---

## Directory Structure Reference

```
app/
├── Contracts/
│   ├── Gateways/          # Gateway interfaces (external integrations)
│   └── Services/          # Service interfaces (domain logic)
├── DataTransferObjects/   # final readonly DTOs
├── Gateways/              # Gateway implementations
├── Http/
│   ├── Controllers/       # Thin controllers only
│   └── Requests/          # FormRequest classes
├── Models/                # Anemic Eloquent models
├── Providers/             # Interface bindings
└── Services/              # Service implementations
```

---

## Reference Implementation

The following example is the **canonical standard** for this project. Every controller must follow this pattern.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\AnimeRecommendationServiceContract;
use App\DataTransferObjects\RecommendationRequestDto;
use App\Http\Requests\GetRecommendationsRequest;
use Illuminate\Http\JsonResponse;

/**
 * Handles anime recommendation HTTP endpoints.
 * Delegates all domain logic to the recommendation service.
 */
final class AnimeRecommendationController extends Controller
{
    public function __construct(
        private readonly AnimeRecommendationServiceContract $recommendationService,
    ) {}

    /**
     * Returns a list of anime recommendations for the authenticated user.
     *
     * @throws \App\Exceptions\RecommendationException
     */
    public function index(GetRecommendationsRequest $request): JsonResponse
    {
        $dto = RecommendationRequestDto::fromArray($request->validated());

        $recommendations = $this->recommendationService->recommend($dto);

        return response()->json($recommendations->toArray());
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Carries validated recommendation request data across layers.
 * Immutable by design — never mutate after construction.
 */
final readonly class RecommendationRequestDto
{
    public function __construct(
        public int $userId,
        public string $genre,
        public int $limit,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            userId: (int) $data['user_id'],
            genre: (string) $data['genre'],
            limit: (int) ($data['limit'] ?? 10),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'genre'   => $this->genre,
            'limit'   => $this->limit,
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DataTransferObjects\RecommendationRequestDto;
use App\DataTransferObjects\RecommendationResultDto;

/**
 * Contract for the anime recommendation domain service.
 */
interface AnimeRecommendationServiceContract
{
    /**
     * Returns recommendations based on the given request criteria.
     *
     * @throws \App\Exceptions\RecommendationException
     */
    public function recommend(RecommendationRequestDto $dto): RecommendationResultDto;
}
```
