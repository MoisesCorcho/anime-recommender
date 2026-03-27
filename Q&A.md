# Q&A: Arquitectura y Decisiones Técnicas
Documentación consolidada sobre las decisiones de diseño, algoritmos y flujos de trabajo del Sistema de Recomendación de Animes.

---

## 0. Glosario de Términos (Conceptos Clave de Arquitectura e IA)

Antes de profundizar en las decisiones técnicas, es vital dominar el vocabulario nativo utilizado en la arquitectura de este sistema de recomendación:

- **Embeddings:** Representaciones matemáticas de datos complejos (textos, sinopsis, géneros). Un modelo de IA (como `text-embedding-3-small` de OpenAI) "lee" el texto del anime y lo procesa hasta convertir su significado conceptual puro en una lista inmensa de números flotantes.
- **Vector (o Vector Semántico):** Es el resultado impreso de un Embedding. Se ve como un array de coordenadas espaciales (ej: `[0.15, -0.9, 0.44...]`). Cada dimensión (espacio matemático) captura una característica conceptual indetectable. Si dos animes tratan de historias de romance escolar trágico, sus vectores tendrán coordenadas de proximidad casi idénticas.
- **pgvector:** Extensión open-source nativa insertada en tu PostgreSQL. Dota a la base de datos relacional del superpoder de soportar columnas de almacenamiento de arreglos vectoriales multidimensionales y realizar operaciones trigonométricas de alta velocidad sobre ellos.
- **Búsqueda Semántica:** A diferencia del SQL relacional normal (`WHERE title LIKE '%ninja%'`), la búsqueda semántica no busca palabras clave, busca **conceptos**. Si tipeás "peleas de artes marciales chinas", aunque la sinopsis de *Dragon Ball* no tenga esas palabras literal, el modelo matemático entenderá por concepto que el anime está semánticamente relacionado y lo devolverá por intuición contextual.
- **HNSW (Hierarchical Navigable Small World):** El sofisticado algoritmo de indexación vectorial que usa pgvector. Sin él, tu base de datos tendría que comparar el vector de la búsqueda contra los 20,000 animes tabla por tabla (Full Table Scan), derritiendo la CPU. El HNSW crea grafos de navegación jerárquica que conectan animes similares, logrando saltos y búsquedas en menos de un milisegundo, sacrificando solo un porcentaje ridículo de precisión matemática.
- **Similitud Coseno (Cosine Similarity):** Es la fórmula geométrica (`<=>`) ejecutada en PostgreSQL para medir literalmente el ángulo formado entre el vector del anime y el vector del usuario. Si el ángulo es chiquitito (Cos = 1), significa que ambos vectores apuntan a la misma esquina conceptual del universo, y el usuario amará ese anime profundamente.
- **Exponential Moving Average (EMA - Promedio Móvil Exponencial):** La fórmula matemática utilizada para re-calcular y actualizar en segundo plano el vector maestro del usuario (`preference_vector`). Esta ecuación tiene la nobleza de darle mucho protagonismo a las calificaciones que el usuario deja hoy, pero manteniendo viva la memoria a largo plazo de sus gustos históricos sin generar cambios violentos en sus recomendaciones solo por cliquear una película ajena a sus gustos de siempre.
- **Event Sourcing (Aproximación Ligera):** Patrón arquitectónico donde el "estado" de las cosas dentro de la Database no se sobrescribe de manera destructiva. En lugar de actualizar y pisar campos estáticos, empujamos transmisiones inmutables del historial humano (todo lo que clica y busca el usuario) a la tabla maestra `user_interactions`. 
- **Arranque en Frío (Cold Start):** La pesadilla de todo arquitecto de Sistemas de Recomendación. Es el desafío analítico de tener que recomendarle contenido brutalmente preciso a un usuario que lleva registrado exactamente 8 segundos y cuyo vector de personalidad y gustos es categóricamente `NULL`.
- **ULID (Universally Unique Lexicographically Sortable Identifier):** A diferencia de insertar por los famosos UUID, que no tienen tiempo, son texto totalmente aleatorio y fraccionan terriblemente la memoria RAM del índice B-Tree en bases de datos relacionales cargadas de Insert-only (como lo es el registro masivo de interacciones de tracking); los ULID insertan la hora en milisegundos crifteada en su composición. Esto genera identificadores globales 100% únicos que se ordenan mágicamente en tu motor de BD dándote consultas con `ORDER BY id DESC` hiper eficientes sin desgastar performance.

---

## 1. Búsqueda Semántica y Flujo de Datos

### ¿Cómo funciona la búsqueda semántica a nivel conceptual?
A diferencia de una búsqueda tradicional (`WHERE title LIKE '%goku%'`), la búsqueda semántica se basa en el **significado** de las palabras. 
1. Todo anime (título, formatos, géneros, sinopsis) se procesa por un modelo de Embeddings de Inteligencia Artificial que lo convierte en un vector (un arreglo matemático de dimensiones, ej: 1536 números).
2. Cuando el usuario busca *"quiero un anime de deporte donde griten mucho"*, el backend toma esa frase y la pasa por el **mismo** modelo de Embeddings, generando un Vector Consulta (*Query Vector*).
3. La base de datos (PostgreSQL con `pgvector`) realiza una operación matemática geométrica (Distancia Coseno o Producto Punto) para encontrar qué vectores de la tabla de animes están "más cerca" del vector consulta. 

### ¿Cuál es el flujo completo (Sincrónico vs Asincrónico) de la búsqueda semántica en la app?
El proceso está dividido para maximizar la velocidad de la Interfaz de Usuario (UI) y evitar cuellos de botella:
- **Primer Plano (Sincrónico):** El usuario escribe en Livewire y le da a buscar. El backend llama a la API de OpenAI instantáneamente para obtener el vector de su consulta, y postgres usa el índice HNSW para traer los animes matemáticamente relevantes. Se devuelven los resultados a la pantalla en milisegundos.
- **Segundo Plano (Asincrónico):** Lo que le devolviste al usuario no se guarda. Lo que **SÍ** se guarda es la *intención* del usuario (su búsqueda o sus clics). Antes de renderizar la vista, Livewire despacha un Job a la cola de Redis (ej. `LogUserInteractionJob`) que trabaja en segundo plano insertando en la base de datos lo que el usuario pidió, sin congelar la pantalla.

---

## 2. Decisiones sobre Modelos de IA (OpenAI)

### ¿Por qué se utiliza el modelo `text-embedding-3-small` (1536 dimensiones)?
Es el estándar de la industria actual de OpenAI por tener el mejor radio de **Costo/Precisión**. Produce vectores de 1536 dimensiones por defecto.
A mayor cantidad de dimensiones, el modelo captura más "matices" y sutilezas semánticas (distinguiendo entre un anime de pelea por odio vs uno de pelea deportiva). Sin embargo, modernos métodos de entrenamiento permiten que modelos como este sean superiores a versiones viejas incluso si se truncan las dimensiones.

### ¿Qué pasa si en el futuro queremos cambiar de modelo de Embeddings (Local o de otra empresa)?
**Los vectores viejos pasan a ser basura.** Los vectores de OpenAI solo tienen sentido en el "espacio matemático" de OpenAI.
El proceso a seguir (Re-embedding) sería:
1. Vaciar la columna de vectores de los Animes y de los Usuarios.
2. Pasar nuevamente todos los animes de la base de datos por el **NUEVO** modelo para generar vectores desde cero.
3. Usar el historial de interacciones guardadas (`user_interactions`) para re-calcular matemáticamente el vector de perfil de los usuarios.

---

## 3. Base de Datos e Interacciones de Usuario

### ¿Por qué se utiliza una sola tabla `user_interactions` en vez de múltiples tablas separadas?
Para evitar **Sobreingeniería**. En lugar de tener `semantic_searches`, `clicks_table`, `filters_table` que romperían el esquema cada vez que hay un requerimiento nuevo, usamos el patrón **Event Sourcing-lite**.
Todo es un "Evento". Se utiliza una tabla única y potente que guarda quién, cuándo y qué se hizo, metiendo la flexibilidad dentro de una columna JSONB.

### ¿Qué datos se guardan estrictamente en la tabla `user_interactions`?
Se utilizan 4 columnas maestras:
- `id` (ULID)
- `user_id` (Relación al usuario, puede ser NULL si es un invitado).
- `type` (Un Backed Enum en PHP, ej: `SEMANTIC_SEARCH`, `CATALOG_FILTER`, `ANIME_VIEW`).
- `payload` (JSONB)
  - *Ejemplo de Semantic Search:* `{"raw_query": "anime de terror", "search_duration_ms": 45}`
  - *Ejemplo de Catalog Filter:* `{"genres": ["shonen"], "seasons": 1}`
  - *Ejemplo de Clic/View:* `{"anime_id": "01H...XYZ"}`

### ¿Por qué utilizamos ULID en lugar de UUID para esta tabla?
Los Identificadores Únicos Universales Ordenables Lexicográficamente (ULID) contienen un timestamp ordenable embebido en su generación. Dado que `user_interactions` es una tabla de Logs (insert-heavy), usar ULIDs evita la fragmentación masiva de los índices en la base de datos que sí provocan los UUID convencionales (los cuales son completamente aleatorios). Además, permiten hacer un `ORDER BY id DESC` y obtener un orden cronológico perfecto gratis.

### ¿Cómo evitamos perder el perfil del usuario tras un Re-embedding?
El Vector del Usuario es un **Estado Derivado**. Si se borra, se recalculan sus gustos usando la verdadera y única **Fuente de la Verdad (Source of Truth)**: la tabla `user_interactions`. Un Job recorre sus interacciones históricas (qué buscó y qué animes cliqueó), busca los vectores de esos animes, y genera un promedio matemático nuevo. Si no guardáramos las interacciones, el aprendizaje del usuario se perdería para siempre al cambiar el modelo.

---

## 4. Colas de Trabajo (Queues y Jobs)

### ¿De qué forma se usan las colas y por qué fueron construidas así?
Las colas (a través de Redis en este stack) se dedican a sacar del proceso principal todas aquellas tareas que son pesadas computacionalmente o que comunican con servicios de terceros (APIs) y que no son estrictamente necesarias para la inmediatez de la Respuesta HTTP.
- **Uso Extensivo (Batching):** El comando `GenerateAnimeEmbeddingsBatchJob` procesa la creación de vectores de 20.000 animes llamando a la API. Se agrupa en "Chunks" (ej. de a 100 animes) enviándolos como un *Batch* de Jobs. Cada Job agarra sus 100 animes, formatea sus textos, llama a la API UNA vez, recibe 100 vectores, y guarda en Base de Datos. Si algo falla, el sistema lo reintenta automáticamente, sin que haya un humano supervisando y sin colgarse por tiempo máximo de ejecución en PHP.
- **Uso Transparente:** Trabajos como `LogUserInteractionJob` registran de fondo qué toca el usuario, cuidando la percepción de inmediatez del Frontend (Livewire).

---

## 5. Diseño de Producto y Modelos Híbridos

### ¿Por qué existe un filtrado manual (búsqueda SQL) si ya tenemos procesamiento por Inteligencia Artificial?
Porque no todo se resuelve con Vectores (IA). Esta arquitectura fomenta la **Búsqueda Híbrida**.
Si el usuario sabe exactamente lo que quiere (Ej: *"Quiero un anime de 2024, formato TV, del género Mecha"*), buscarlo vectorialmente consume dinero de OpenAI de manera innecesaria y es menos exacto. Para intenciones explícitas estrictas, las sentencias SQL clásicas ganan en rendimiento y fiabilidad. La IA reluce cuando el usuario intenta explicar "sentimientos", "vibras" o conceptos no etiquetables estáticamente.

## 6. Dimensiones de Modelos de Embeddings

### ¿Cuántas dimensiones tiene el modelo text-embedding-3-small?
Por defecto, el modelo devuelve vectores de **1536 dimensiones**.  
Sin embargo, permite usar un parámetro opcional (`dimensions`) para reducir el tamaño del vector (por ejemplo, a 512 o 256), manteniendo buena calidad.  
Si no se especifica este parámetro, siempre generará vectores de 1536 dimensiones.

---

## 7. Restricción de Dimensiones en la Base de Datos

### ¿La columna de la base de datos debe tener exactamente la misma cantidad de dimensiones que el modelo?
Sí. Es una restricción estricta a nivel matemático y de base de datos (por ejemplo, usando pgvector).  
No es posible almacenar ni comparar vectores de diferentes dimensiones en la misma columna. Si se intenta, se producirá un error.

---

## 8. Cambio de Modelo de Embeddings

### ¿Qué sucede si se cambia de modelo de embeddings en el futuro?
Al cambiar de modelo, los vectores previamente almacenados dejan de ser útiles, ya que cada modelo genera representaciones en un “espacio semántico” diferente.  
Esto implica que los embeddings antiguos no son compatibles con los nuevos.

La solución es realizar un proceso de **re-embedding**, que consiste en:
- Tomar todos los datos originales
- Generar nuevamente los embeddings usando el nuevo modelo
- Reemplazar los vectores antiguos en la base de datos

---

## 9. Estándares de Dimensiones en Embeddings

### ¿1536 dimensiones es un estándar en la industria?
No existe un estándar universal para la cantidad de dimensiones en embeddings.  
Cada modelo define su propio tamaño según su arquitectura y optimización.

Ejemplos comunes:
- Modelos ligeros (como MiniLM): ~384 dimensiones  
- Modelos tipo BERT: ~768 dimensiones  
- Otros proveedores: ~1024 dimensiones  
- Modelos grandes: hasta 3072 dimensiones  

La elección de dimensiones depende del balance entre:
- rendimiento
- costo
- precisión semántica  

---

## 10. Estrategia de Arquitectura para Embeddings

### ¿Cómo diseñar el sistema para soportar cambios de modelo en el futuro?
Se recomienda abstraer la generación de embeddings mediante una interfaz en la aplicación (por ejemplo, en Laravel).  
De esta forma:
- el modelo de embeddings puede cambiarse sin afectar la lógica de negocio
- solo se reemplaza la implementación concreta

Sin embargo, el cambio de modelo siempre implicará un proceso de **re-embedding** de todos los datos existentes.

## 11. Búsqueda Híbrida en Sistemas de Recomendación

### ¿Por qué es obligatorio mantener una búsqueda manual (SQL) si ya se usan embeddings?
Porque no todo se resuelve con vectores. Esta arquitectura implementa una **Búsqueda Híbrida**.

Cuando el usuario tiene una intención clara y estructurada (por ejemplo: año, género, temporadas), las consultas SQL son más rápidas, precisas y económicas que una búsqueda semántica.

La búsqueda vectorial se utiliza cuando la intención es difusa o conceptual (por ejemplo: *"quiero algo triste pero inspirador"*), o como mecanismo de ordenamiento posterior para mejorar la relevancia de los resultados obtenidos mediante filtros tradicionales.

---

## 12. Recolección de Señales del Usuario

### ¿Por qué es importante guardar las búsquedas manuales del usuario?
Porque representan **Señales Explícitas (Explicit Signals)** dentro de un sistema de recomendación.

Cuando un usuario aplica filtros manuales, está indicando directamente sus preferencias. Esta información es valiosa para:
- entender el comportamiento del usuario
- alimentar algoritmos de recomendación
- personalizar futuras interacciones

---

## 13. Modelado de Interacciones de Usuario

### ¿Cómo se deben almacenar las búsquedas e interacciones del usuario?
Se recomienda crear una tabla dedicada (por ejemplo: `user_interactions` o `user_search_logs`) que contenga:

- `user_id`
- `search_type` (ej: `manual_filter`, `semantic_search`)
- `filter_payload` (JSON con los filtros aplicados)
- `created_at`

Esto permite registrar de forma estructurada el comportamiento del usuario y utilizarlo posteriormente para análisis o personalización.

---

## 14. Vectorización del Comportamiento del Usuario

### ¿Se deben vectorizar directamente los filtros aplicados por el usuario?
No. Los filtros estructurados (como número de temporadas o año) no deben vectorizarse.

En lugar de eso, se utiliza la interacción del usuario con los resultados (clics, favoritos, visualizaciones) para actualizar su representación vectorial.

---

## 15. Vector de Preferencias del Usuario

### ¿Qué es el vector del usuario y para qué sirve?
Es un vector que representa las preferencias del usuario dentro del mismo espacio semántico que los embeddings de los animes.

Este vector evoluciona con el tiempo en función de las interacciones del usuario y permite:
- recomendar contenido personalizado
- ordenar resultados según afinidad
- construir sistemas de recomendación dinámicos

---

## 16. Almacenamiento del Vector del Usuario

### ¿Dónde se debe almacenar el vector de preferencias del usuario?
Existen dos enfoques principales:

**Base de datos relacional (PostgreSQL + pgvector):**
- Se añade una columna `preference_vector` en la tabla `users`

**Base de datos vectorial externa (Pinecone, Qdrant):**
- Se almacena el vector separado junto con metadata (`user_id`)

La elección depende de la escala del sistema y los requerimientos de rendimiento.

---

## 17. Actualización del Perfil del Usuario (Matemática de la Vectorización)

### ¿Cómo se genera exactamente la actualización del perfil del usuario (preference_vector) con ejemplos?
El vector del usuario es una representación fluida de sus gustos. Para que este perfil "aprenda" con cada interacción sin olvidar drásticamente lo que le gustaba ayer, se utiliza una fórmula matemática llamada **Decaimiento Exponencial** o **Promedio Móvil Exponencial (EMA - Exponential Moving Average)**.

**Fórmula Conceptual:**
`Nuevo_Vector = (Vector_Viejo * α) + (Vector_Anime_Visto * (1 - α))`
Donde `α` (alfa) es el "factor de retención de memoria" (ej: 0.8 u 80%).

**Ejemplo Práctico:**
Imagina que trabajamos con vectores de solo 3 dimensiones (en vez de 1536) para que sea humanamente legible: `[Acción, Comedia, Romance]`.

1. **Estado Inicial:** Un nuevo usuario se registra. Su vector comienza en cero: 
   `U0 = [0.0, 0.0, 0.0]`

2. **Interacción 1:** El usuario marca como "Favorito" el anime "Dragon Ball Z" (puro shonen de acción). Su vector es: 
   `A1 = [0.9, 0.1, 0.0]`
   Como el usuario es nuevo, su vector absorbe este anime completamente:
   `U1 = [0.9, 0.1, 0.0]` *(Al sistema le queda claro que le encanta la acción)*

3. **Interacción 2:** Días después, el usuario ve "Kaguya-sama" (RomCom, 0 acción, alta comedia y romance). Su vector es:
   `A2 = [0.0, 0.8, 0.9]`
   
   Aplicamos la fórmula EMA usando un factor de memoria histórica del 80% (`α = 0.8`) y dándole al anime reciente una fuerza del 20% `(1 - 0.8 = 0.2)`:
   
   - Multiplicamos historia vieja por 0.8: `[0.9 * 0.8, 0.1 * 0.8, 0.0 * 0.8] = [0.72, 0.08, 0.0]`
   - Multiplicamos anime nuevo por 0.2: `[0.0 * 0.2, 0.8 * 0.2, 0.9 * 0.2] = [0.0, 0.16, 0.18]`
   - Sumamos los resultados componente por componente: `[0.72 + 0.0, 0.08 + 0.16, 0.0 + 0.18]`
   
   **Nuevo Perfil Matemático (`U2`) = `[0.72, 0.24, 0.18]`**

**Conclusión del ejemplo:** El sistema se adapta y "se da cuenta" de que la acción sigue siendo su género fundacional dominante (0.72), pero gradualmente modela un gusto emergente por la comedia (0.24) y el romance (0.18). Esta simple pero brillante operación multiplicada por 1536 dimensiones es lo que corre en tus Jobs asincrónicos cada vez que un usuario interactúa.

---

## 18. Personalización de Resultados

### ¿Cómo se utilizan los vectores del usuario para mejorar la experiencia?
El vector del usuario se usa para calcular similitud con los embeddings de los animes.

Esto permite:
- ordenar resultados según afinidad
- generar recomendaciones personalizadas
- mejorar la relevancia sin necesidad de nuevas búsquedas explícitas

Por ejemplo, al cargar la página principal, se pueden ordenar los animes por cercanía al `preference_vector` del usuario.

## 19. How can I export a specific table (e.g., `animes`) from my PostgreSQL database using Laravel Sail?


### Answer
Run the following command from your project root to execute `pg_dump` within the Sail container:

```bash
./vendor/bin/sail shell -c "pg_dump -U sail -h pgsql -t animes --inserts laravel" > animes_backup.sql
```

---

## 20. Arquitectura: El Patrón del Modelo Pivote (`Pivot Model`)

### ¿Es una "Best Practice" crear un Modelo propio para la tabla intermedia `anime_user`?
Mundialmente reconocido como un **Sí rotundo**. En la arquitectura Clean de Laravel 13, cuando una tabla pivote o intermedia deja de ser un simple nexo de dos IDs (`user_id`, `anime_id`) y crece para albergar estado del dominio (`status`, `is_favorite`, `episodes_watched`, `score`), se transforma en una **Entidad del Dominio**.

La práctica definitiva acá es crear una clase que herede directamente de la clase Pivot de Eloquent (ejemplo: `class AnimeUser extends Pivot`). 

**Ventajas tangibles y potentes:**
1. **Tipado Fuerte y Mapeo Geométrico (Casts):** Podés definir directamente en el pivot model que la columna `status` se convierta automáticamente e irreversiblemente en tu Backed Enum en PHP (`UserAnimeStatus::class`), y castear `is_favorite` nativamente a booleano. Se acaban los condicionales sucios en tu backend.
2. **Disparo de Eventos del Ciclo de Vida (Observers vitales):** A diferencia de insertar con `DB::table()`, un modelo pivote dispara los eventos clásicos de Eloquent (`created`, `updated`, `saved`). Esta característica es un "Game Changer": si el usuario actualiza una tarjeta a `COMPLETED`, el `Observer` del pivote atrapa ese salvado y automáticamente despacha el Job asincrónico para calcular el nuevo `preference_vector` (usando la matemática descrita en el punto 17). Toda la recolección semántica queda completamente escondida y desacoplada de tus controladores de Livewire.
3. **Mutators Centralizados:** Permite blindar reglas de dominio en el guardado (por ejemplo, asegurar matemáticamente que la columna de `score` jamás reciba un número mayor a 10 ni menor a 1, aunque el usuario haya inyectado algo roto en el frontend).

---

## 21. El Problema del "Arranque en Frío" (Cold Start)

### Si un usuario nuevo se registra y solo usa el buscador de filtros, ¿cuándo se le asigna su primer vector?
El perfil inicial del usuario (su `preference_vector`) nace como **nulo (NULL)**. Realizar búsquedas usando los filtros del catálogo (ej: *"Seinen del 2017"*) **no le asigna un vector** de forma automática.

El vector se inicializa estrictamente en base a las **Reglas de Ponderación de tu Dominio (Interaction Weights)**. Existen dos caminos arquitectónicos para inicializarlo:

1. **El Camino Agresivo (Señales Implícitas Débiles):** En cuanto el usuario da *clic en una tarjeta de anime filtrado* (para ver sus detalles), considerás ese clic como suficiente interés. Inicializás su vector absorbiendo el 100% de ese anime que miró. Es rápido, pero estadísticamente riesgoso (el usuario puede haber hecho un "clic accidental" o por simple curiosidad por la portada, sesgando todas sus futuras recomendaciones).
2. **El Camino Conservador (Señales Explícitas Fuertes - RECOMENDADO):** Esperás a que el usuario declare intenciones serias. Su vector sigue siendo nulo hasta el instante en que agregue su primer anime a **"Viendo" (WATCHING)**, **"Completado" (COMPLETED)**, o **"Favorito"**. Hasta que eso ocurra, le mostrás en la Home los animes *"Más Populares"* de la base de datos de manera genérica. Cuando ejecuta una de estas acciones explícitas, absorbe la identidad geométrica del vector de ese anime al 100%. Sistemas como Netflix evitan esto último obligándote en el onboarding a "Seleccionar 3 películas que te gusten" para inicializarte tu vector de golpe.

---

## 22. Vectores Negativos (La Lista Negra)

### ¿Cómo afecta matemáticamente al vector cuando un usuario pone un anime en "Blacklist"?
Existen dos formas de manejar una penalización en sistemas de recomendación semánticos:

1. **El Filtro Duro por SQL (La Arquitectura Correcta):** 
   El usuario interactúa y manda algo a Blacklist. **No se recalculan las matemáticas del vector**. Su vector queda intacto. Simplemente lo guardás en tu tabla pivote con estado `BLACKLISTED`. Luego, a nivel base de datos, toda recomendación semántica o query SQL lleva adherida la cláusula: `WHERE animes.id NOT IN (lista_negra_uuid)`. Ocultás el contenido no deseado **preservando perfecto** el perfil de gustos generales del usuario.

2. **La Inversa Vectorial (El Anti-Patrón):**
   Mandar un anime a la Blacklist empuja matemáticamente tu vector en dirección opuesta (se re-promedia restando en lugar de sumando). **Es un error de diseño grave.** Si al usuario le fascina el *Shonen*, pero odia profundamente a *Naruto* y lo mete en su lista negra, restar matemáticamente a Naruto de su perfil lo va a alejar genéricamente de TODO el concepto de Shonen de forma injusta. El usuario generalmente odia una obra individual, no las dimensiones semánticas geográficas que la componen.

---

## 23. Contaminación por Búsquedas Curiosas

### ¿Se debe actualizar el vector de un usuario cuando hace una búsqueda con texto libre o usa los filtros del sistema?
**Definitivamente No.** En Arquitectura de Datos: *Buscar (*Searching*) no equivale a una preferencia intrínseca (*Preferring*).*

Archivar los filtros aplicados en el log (`user_interactions`) es inmensamente valioso para la **Analítica del Negocio** (saber de qué año pide más el público, armar métricas, dashboards). Pero si modificás la estructura del ADN vectorial del usuario basándote solo en lo que "buscó un martes por la tarde", rompes el sistema de recomendaciones a largo plazo.

Si un usuario busca algo muy raro (ej. "Hentai") por pura curiosidad morbosa o una broma con amigos en el navegador, y tu sistema absorbe ciegamente esas dimensiones a su perfil por el simple acto de usar el buscador, le arruinarás el catálogo de su Home por meses. El vector de identidad se blinda; solo se entra a él confirmando el interés mediante interacciones directas con el contenido (Clic Prolongado, Rating, Viendo, o Favorito).

---

## 24. Patrón Action Class

### ¿Las clases dentro de `app/Actions/` son una feature de Laravel o un patrón de arquitectura?

Son un **patrón de arquitectura**, no una feature de Laravel. No existe `artisan make:action`. La comunidad Laravel adoptó masivamente el patrón de **Single Action Classes**, que viene del Command Pattern del diseño de software. Se crean con `artisan make:class Actions/NombreAction` o simplemente como archivos PHP planos — no hay magia de framework adentro.

### ¿Qué problema resuelven?

El enemigo es el **Fat Controller** y el **Fat Model**. Sin Actions, la lógica de negocio termina acumulada en controladores que mezclan validación, reglas de dominio y persistencia en decenas de líneas. El resultado es código que no se puede testear en aislamiento y no se puede reutilizar desde otro punto de entrada.

### ¿Un Action es básicamente un método privado de un Service extraído a su propia clase?

Exactamente. Un Action **es lo que escribirías como método privado en un Service, pero promovido a clase propia**. La diferencia crítica es lo que ganás al extraerlo:

- **Un método privado** queda atrapado en su clase: para testearlo tenés que pasar por el método público que lo llama, no podés instanciarlo en aislamiento, y si otro Service necesita la misma lógica la reescribís.
- **Un Action** es una clase pública: inyectable vía el container de Laravel, testeable directamente, reutilizable desde cualquier punto de entrada (Controller, Job, Command de Artisan, otro Service).

```php
// ❌ Lógica atrapada — no testeable en aislamiento
class CreditService
{
    public function deductForSemanticSearch(User $user): void
    {
        $this->checkBalance($user);   // método privado
        $this->deductCredit($user);   // método privado
    }

    private function checkBalance(User $user): void { ... }
    private function deductCredit(User $user): void { ... }
}

// ✅ Lógica extraída — testeable y reutilizable
class CheckCreditBalanceAction
{
    public function execute(User $user): void
    {
        if ($user->credit_balance < 1) {
            throw new InsufficientCreditsException();
        }
    }
}
```

Con el Action extraído, el test es directo:

```php
it('throws when balance is zero', function () {
    $user = User::factory()->create(['credit_balance' => 0]);

    expect(fn() => (new CheckCreditBalanceAction())->execute($user))
        ->toThrow(InsufficientCreditsException::class);
});
```

### ¿Cuándo un método privado debe quedarse privado y cuándo debe convertirse en Action?

Un método privado está bien si es trivial y específico de esa clase. Merece ser un Action cuando tiene **lógica de negocio real**, un **nombre descriptivo con semántica clara**, y **potencial de ser llamado desde más de un lugar**. La regla práctica: si el método tiene nombre de operación de negocio (`deductCredit`, `checkBalance`, `initializeCredits`), es candidato a Action.

### ¿Cuál es la diferencia entre un Action y un Service?

| | Action | Service |
|---|---|---|
| **Responsabilidad** | Una operación atómica específica | Orquestación de varias operaciones |
| **Estado** | Sin estado (stateless) | Tiene dependencias inyectadas |
| **Ejemplo en este sistema** | `DeductCreditAction` | `CreditService` |
| **Analogía** | Un tornillo | El destornillador |

Un Service usa varios Actions. Un Action no usa otros Actions — si eso empieza a pasar, la lógica sube al Service.