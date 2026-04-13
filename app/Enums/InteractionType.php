<?php

declare(strict_types=1);

namespace App\Enums;

enum InteractionType: string
{
    case SemanticSearch = 'SEMANTIC_SEARCH';
    case CatalogFilter = 'CATALOG_FILTER';
    case AnimeView = 'ANIME_VIEW';
    case FavoriteAdd = 'FAVORITE_ADD';

    /**
     * @return array{query: string, results_count: int}
     */
    public static function semanticSearchPayload(string $query, int $resultsCount): array
    {
        return [
            'query' => $query,
            'results_count' => $resultsCount,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{filters: array<string, mixed>, results_count: int}
     */
    public static function catalogFilterPayload(array $filters, int $resultsCount): array
    {
        return [
            'filters' => $filters,
            'results_count' => $resultsCount,
        ];
    }

    /**
     * @return array{anime_id: string}
     */
    public static function animeViewPayload(string $animeId): array
    {
        return [
            'anime_id' => $animeId,
        ];
    }

    /**
     * @return array{anime_id: string}
     */
    public static function favoriteAddPayload(string $animeId): array
    {
        return [
            'anime_id' => $animeId,
        ];
    }

    /**
     * Human-readable label for display in UI or logs.
     */
    public function label(): string
    {
        return __("enums.interaction_type.{$this->value}");
    }

    /**
     * All cases as a value => label map, ready for select inputs.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(
            array_map(
                fn (self $case): array => ['value' => $case->value, 'label' => $case->label()],
                self::cases(),
            ),
            'label',
            'value',
        );
    }
}
