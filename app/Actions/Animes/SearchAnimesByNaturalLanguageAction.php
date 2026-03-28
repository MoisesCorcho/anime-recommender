<?php

declare(strict_types=1);

namespace App\Actions\Animes;

use App\Models\Anime;
use Illuminate\Database\Eloquent\Collection;

/**
 * Executes a semantic similarity search against the anime catalogue.
 *
 * Uses `orderByVectorDistance` — the framework-native way to rank records
 * purely by cosine distance without any similarity threshold. The query string
 * is automatically vectorised by the framework using the configured AI provider
 * (text-embedding-3-small). Only animes that already have an embedding stored
 * are eligible results.
 *
 * The embedding column is excluded from the returned models — consumers only
 * need the display fields.
 */
final class SearchAnimesByNaturalLanguageAction
{
    /** Columns returned to the caller — excludes the large embedding vector. */
    private const SELECTED_COLUMNS = [
        'id', 'mal_id', 'title', 'description',
        'image_url', 'type', 'episodes', 'status',
        'released_year', 'genres', 'score',
    ];

    /**
     * Return the top $limit animes closest in meaning to the given query.
     *
     * @return Collection<int, Anime>
     */
    public function execute(string $query, int $limit = 10): Collection
    {
        return Anime::query()
            ->select(self::SELECTED_COLUMNS)
            ->whereNotNull('embedding')
            ->orderByVectorDistance('embedding', $query)
            ->limit($limit)
            ->get();
    }
}
