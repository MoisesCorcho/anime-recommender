<?php

declare(strict_types=1);

namespace App\Enums;

enum InteractionType: string
{
    case SemanticSearch = 'SEMANTIC_SEARCH';
    case CatalogFilter = 'CATALOG_FILTER';
    case AnimeView = 'ANIME_VIEW';
    case FavoriteAdd = 'FAVORITE_ADD';
}
