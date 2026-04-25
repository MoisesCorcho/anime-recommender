<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Immutable data transfer object for creating an Anime record.
 *
 * Used by seeders or services to hydrate the database safely,
 * decoupling the raw CSV/API data from the Eloquent model.
 */
final readonly class CreateAnimeDTO
{
    /**
     * @param  int  $malId  MyAnimeList unique identifier.
     * @param  string  $title  Canonical anime title.
     * @param  string|null  $description  Plot synopsis or overview.
     * @param  string|null  $imageUrl  URL to the cover image.
     * @param  string|null  $type  Format type (e.g. TV, Movie, OVA).
     * @param  int|null  $episodes  Total episode count.
     * @param  string|null  $status  Airing status (e.g. Finished Airing).
     * @param  int|null  $releasedYear  Year the anime first aired.
     * @param  list<string>  $genres  Normalized list of genre names.
     * @param  float|null  $score  MAL community score (0–10).
     * @param  list<float>|null  $embedding  1536-dimensional embedding vector.
     */
    public function __construct(
        public readonly int $malId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $imageUrl,
        public readonly ?string $type,
        public readonly ?int $episodes,
        public readonly ?string $status,
        public readonly ?int $releasedYear,
        public readonly array $genres,
        public readonly ?float $score,
        public readonly ?array $embedding = null,
    ) {}
}
