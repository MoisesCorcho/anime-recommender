# Anime Recommender

A semantic anime recommendation system built with Laravel 13, powered by vector embeddings and pgvector. The system imports ~20,000 anime titles from MyAnimeList, generates 1536-dimensional embeddings via OpenAI, and enables natural-language similarity search through pgvector's HNSW index.

---

## Purpose

Traditional search filters by exact keywords. This system lets users describe what they want — *"a dark psychological thriller set in a dystopian future"* — and returns animes semantically similar to that description, even if none of the words match exactly.

The architecture is designed to be extended: user interactions are tracked and aggregated into a personal preference vector, enabling truly personalized recommendations over time.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                         User Request                            │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│              Livewire + Volt (Blade UI, no SPA)                 │
│         Authentication via Laravel Breeze (Livewire stack)      │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Domain Layer                                │
│                                                                 │
│  Actions/              Services/Ai/          Jobs/              │
│  ├─ BuildSemantic      └─ AnimeEmbedding     ├─ GenerateAnime   │
│  │  TextForEmbedding      Service               EmbeddingsB     │
│  │  Action                (wraps Laravel        atchJob         │
│  │  (text construction)    AI SDK)           └─ LogUserInter    │
│  │                                               actionJob      │
│  DataTransferObjects/                                           │
│  ├─ CreateAnimeDTO     (CSV row → DB)                           │
│  └─ AnimeEmbeddingTextDTO  (id + semantic text)                 │
│                                                                 │
│  Enums/                                                         │
│  └─ InteractionType    (SEMANTIC_SEARCH, CATALOG_FILTER,        │
│                         ANIME_VIEW, FAVORITE_ADD)               │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Infrastructure Layer                         │
│                                                                 │
│  PostgreSQL 18 + pgvector extension                             │
│  ├─ animes.embedding        vector(1536) + HNSW index           │
│  ├─ users.preference_vector vector(1536)                        │
│  └─ user_interactions.payload jsonb                             │
│                                                                 │
│  Redis               → Queue backend + Cache                    │
│  OpenAI API          → text-embedding-3-small (1536 dims)       │
│  Laravel Queue       → Batchable jobs with retry/backoff        │
└─────────────────────────────────────────────────────────────────┘
```

### Key Design Decisions

| Decision | Rationale |
|---|---|
| **ULID primary keys** | Sortable, URL-safe, collision-free — better than UUID for ordered tables |
| **pgvector HNSW index** | Approximate nearest-neighbor search at scale — O(log n) vs O(n) brute-force |
| **1536 dimensions** | Matches `text-embedding-3-small` native output — best cost/quality ratio |
| **Batch jobs via Bus::batch()** | Each job = 1 OpenAI API call for N animes — minimizes round trips and tracks progress |
| **Action + Service + DTO pattern** | Single Responsibility: text building, API calls, and persistence are fully isolated and independently testable |
| **LazyById + Builder::chunk()** | Seeder loads CSV in 500-row chunks; embedding command paginates DB cursor-based |
| **Livewire + Volt** | Full-stack reactive UI without a JavaScript SPA — same server-side simplicity as Blade |
| **Redis for queues** | Jobs processed async — embedding 20k animes runs in the background without blocking |
| **jsonb for interaction payload** | Flexible per-type metadata without schema changes |

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 / PHP 8.5 |
| Frontend | Livewire 3, Volt 1, Tailwind CSS 3, Vite 8 |
| Authentication | Laravel Breeze (Livewire stack) + Sanctum |
| Database | PostgreSQL 18 + pgvector extension |
| Cache & Queue | Redis |
| AI / Embeddings | Laravel AI SDK (`laravel/ai`) → OpenAI |
| Testing | Pest 4 |
| Docker | Laravel Sail |
| Code Style | Laravel Pint |

---

## Project Structure

```
app/
├── Actions/Animes/
│   └── BuildSemanticTextForEmbeddingAction.php  # builds "Title: X. Format: Y. Genres: ..." text
├── Console/Commands/
│   └── GenerateAnimeEmbeddingsCommand.php        # artisan anime:generate-embeddings
├── DataTransferObjects/
│   ├── AnimeEmbeddingTextDTO.php                 # (animeId, text) pair
│   └── CreateAnimeDTO.php                        # typed DTO for CSV seeding
├── Enums/
│   └── InteractionType.php                       # user event types
├── Jobs/
│   ├── GenerateAnimeEmbeddingsBatchJob.php        # queued, batchable, 3 retries
│   └── LogUserInteractionJob.php                 # async interaction logging
├── Models/
│   ├── Anime.php                                 # ULID, vector(1536), genres cast
│   ├── User.php                                  # preference_vector(1536), HasMany interactions
│   └── UserInteraction.php                       # ULID, enum cast, BelongsTo User
└── Services/Ai/
    └── AnimeEmbeddingService.php                 # wraps Embeddings::for()->generate()

database/
├── data/
│   ├── anime-dataset-2025.csv                    # ~38MB — seeder source (~20k records)
│   └── anime-dataset-2023.csv                    # legacy snapshot
├── migrations/
│   ├── …create_animes_table.php                  # vector(1536) + HNSW index
│   ├── …add_preference_vector_to_users_table.php
│   └── …create_user_interactions_table.php       # jsonb payload
├── seeders/
│   └── AnimeSeeder.php                           # SplFileObject CSV parser, upserts by mal_id
└── factories/
    ├── AnimeFactory.php
    ├── UserFactory.php
    └── UserInteractionFactory.php

tests/
├── Feature/
│   ├── GenerateAnimeEmbeddingsBatchJobTest.php   # uses Embeddings::fake()
│   ├── LogUserInteractionTest.php
│   └── Auth/                                     # Breeze auth flows
└── Unit/
    └── Actions/Animes/
        └── BuildSemanticTextForEmbeddingActionTest.php  # 6 tests, no DB
```

---

## Installation

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or OrbStack on macOS)
- PHP 8.2+ and Composer (only needed for the first `composer install`)
- Node.js 18+

### 1 — Clone and configure

```bash
git clone <repository-url> anime-recommender
cd anime-recommender

cp .env.example .env
```

Open `.env` and set your OpenAI API key — required for embedding generation:

```env
OPENAI_API_KEY=sk-proj-...
```

The database, Redis, and mail settings are pre-configured for Sail and work out of the box.

### 2 — Install dependencies

```bash
composer install
npm install
```

### 3 — Start Docker services

```bash
vendor/bin/sail up -d
```

This starts four containers:

| Container | Purpose | Port |
|---|---|---|
| `laravel.test` | PHP 8.5 app | `localhost:80` |
| `pgsql` | PostgreSQL 18 + pgvector | `localhost:5432` |
| `redis` | Cache + Queue | `localhost:6379` |
| `mailpit` | Email testing | `localhost:8025` |

### 4 — Generate app key and run migrations

```bash
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan migrate
```

The migrations automatically enable the `vector` pgvector extension and create the HNSW index on `animes.embedding`.

### 5 — Import the anime dataset

The CSV is already included in `database/data/`. Run the seeder:

```bash
vendor/bin/sail artisan db:seed
```

This will import approximately **19,931 anime records** from `anime-dataset-2025.csv` using upsert by `mal_id` — safe to re-run multiple times.

Expected output:

```
Importing anime dataset...
  → 500 records imported...
  → 1000 records imported...
  ...
Done. Imported: 19931 | Skipped: 1
```

### 6 — Build frontend assets

```bash
vendor/bin/sail npm run build
```

Or for development with hot reload:

```bash
vendor/bin/sail npm run dev
```

### 7 — Generate embeddings

This step calls the OpenAI API. Make sure `OPENAI_API_KEY` is set in your `.env`.

The command dispatches batched queue jobs — each job sends up to 100 animes to OpenAI in a single API call:

```bash
# In terminal 1 — start the queue worker
vendor/bin/sail artisan queue:work

# In terminal 2 — dispatch the embedding jobs
vendor/bin/sail artisan anime:generate-embeddings
```

**Options:**

```bash
# Smaller chunks (fewer animes per API call — safer for rate limits)
vendor/bin/sail artisan anime:generate-embeddings --chunk=50

# Re-generate embeddings for animes that already have one
vendor/bin/sail artisan anime:generate-embeddings --fresh

# Both options combined
vendor/bin/sail artisan anime:generate-embeddings --fresh --chunk=50
```

**Cost estimate:** At ~20k animes and `text-embedding-3-small` pricing (~$0.02 / 1M tokens), the full dataset costs roughly **$0.05–$0.15 USD** depending on description lengths.

**Monitor progress:** The batch logs completion and failures to `storage/logs/laravel.log`. You can also tail logs live:

```bash
vendor/bin/sail artisan pail
```

### 8 — Open the application

```bash
vendor/bin/sail open
```

Or navigate to [http://localhost](http://localhost).

---

## Running Tests

```bash
# Full suite
vendor/bin/sail artisan test --compact

# Single file
vendor/bin/sail artisan test --compact tests/Unit/Actions/Animes/BuildSemanticTextForEmbeddingActionTest.php

# By name filter
vendor/bin/sail artisan test --compact --filter=GenerateAnimeEmbeddings
```

Tests use `Embeddings::fake()` from the Laravel AI SDK — no real API calls are made during testing.

---

## Useful Commands

```bash
# List all artisan commands
vendor/bin/sail artisan list

# Inspect database schema
vendor/bin/sail artisan tinker --execute "DB::select('SELECT column_name, data_type FROM information_schema.columns WHERE table_name = \'animes\'')"

# Check embedding progress
vendor/bin/sail artisan tinker --execute "echo App\Models\Anime::whereNotNull('embedding')->count() . ' / ' . App\Models\Anime::count();"

# View routes
vendor/bin/sail artisan route:list

# Format code (Pint)
vendor/bin/sail bin pint

# Stop all containers
vendor/bin/sail stop

# Destroy containers and volumes (full reset)
vendor/bin/sail down --volumes
```

---

## Database Schema

### `animes`

| Column | Type | Description |
|---|---|---|
| `id` | ULID | Primary key — sortable, URL-safe |
| `mal_id` | bigint (unique) | MyAnimeList ID — used for deduplication |
| `title` | string | Canonical anime title |
| `description` | text | Plot synopsis — source text for embedding |
| `image_url` | string | MAL cover image CDN URL |
| `type` | string | Format: TV, Movie, OVA, ONA, Special, Music |
| `episodes` | integer | Total episode count (null for movies/ongoing) |
| `status` | string | Finished Airing / Currently Airing / Not yet aired |
| `released_year` | integer | Year of first broadcast |
| `genres` | json | Array of genre names: `["Action", "Fantasy"]` |
| `score` | decimal(4,2) | MAL community score 1.00–10.00 |
| `embedding` | vector(1536) | HNSW-indexed embedding for similarity search |

### `users`

| Column | Type | Description |
|---|---|---|
| `id` | bigint | Primary key |
| `name` | string | Display name |
| `email` | string (unique) | Login email |
| `password` | string | Hashed |
| `preference_vector` | vector(1536) | Aggregated user taste embedding |

### `user_interactions`

| Column | Type | Description |
|---|---|---|
| `id` | ULID | Primary key |
| `user_id` | foreignId (nullable) | Null for guest interactions |
| `type` | string | `SEMANTIC_SEARCH`, `CATALOG_FILTER`, `ANIME_VIEW`, `FAVORITE_ADD` |
| `payload` | jsonb | Event-specific metadata (query text, filters, anime ID, etc.) |

---

## Embedding Pipeline

```
anime:generate-embeddings
         │
         ▼
 Anime::whereNull('embedding')
         │
         │  chunks of N (default 100)
         ▼
 GenerateAnimeEmbeddingsBatchJob  ←  dispatched via Bus::batch()
         │
         │ 1. Fetch animes from DB (id, title, description, genres, type)
         │
         ▼
 BuildSemanticTextForEmbeddingAction
         │  builds: "Title: X. Format: TV. Genres: Action, Drama. Synopsis: ..."
         │  returns: AnimeEmbeddingTextDTO(animeId, text)
         │
         ▼
 AnimeEmbeddingService::generate()
         │  Embeddings::for($texts)->dimensions(1536)->generate()
         │  1 API call per job, all texts in batch
         │
         ▼
 DB::transaction()
         │  UPDATE animes SET embedding = '[...]' WHERE id = ?
         │  (Query Builder bypass — model events not needed here)
         ▼
         ✓ Vector stored, ready for similarity search
```

---

## Services (Docker)

| Service | Image | Ports |
|---|---|---|
| App | `sail-8.5/app` (PHP 8.5) | `80` (HTTP), `5173` (Vite) |
| Database | `pgvector/pgvector:pg18` | `5432` |
| Cache/Queue | `redis:alpine` | `6379` |
| Mail | `axllent/mailpit` | `1025` (SMTP), `8025` (Dashboard) |
| Tunnel | `ngrok/ngrok:alpine` | `4040` (API) |
