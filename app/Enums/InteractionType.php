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
     * Human-readable label for display in UI or logs.
     */
    public function label(): string
    {
        return match ($this) {
            self::SemanticSearch => 'Semantic Search',
            self::CatalogFilter => 'Catalog Filter',
            self::AnimeView => 'Anime View',
            self::FavoriteAdd => 'Favorite Add',
        };
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
