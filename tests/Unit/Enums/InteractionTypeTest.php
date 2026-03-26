<?php

declare(strict_types=1);

use App\Enums\InteractionType;

it('returns a human-readable label for each case', function (InteractionType $case, string $expected) {
    expect($case->label())->toBe($expected);
})->with([
    [InteractionType::SemanticSearch, 'Semantic Search'],
    [InteractionType::CatalogFilter,  'Catalog Filter'],
    [InteractionType::AnimeView,      'Anime View'],
    [InteractionType::FavoriteAdd,    'Favorite Add'],
]);

it('returns all cases as a value => label map via options()', function () {
    $options = InteractionType::options();

    expect($options)->toBe([
        'SEMANTIC_SEARCH' => 'Semantic Search',
        'CATALOG_FILTER' => 'Catalog Filter',
        'ANIME_VIEW' => 'Anime View',
        'FAVORITE_ADD' => 'Favorite Add',
    ]);
});

it('options() contains one entry per case', function () {
    expect(InteractionType::options())->toHaveCount(count(InteractionType::cases()));
});
