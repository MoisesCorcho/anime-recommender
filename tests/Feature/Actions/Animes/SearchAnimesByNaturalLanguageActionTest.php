<?php

declare(strict_types=1);

use App\Actions\Animes\SearchAnimesByNaturalLanguageAction;
use App\Models\Anime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Embeddings;

uses(RefreshDatabase::class);

it('returns a collection of Anime models', function (): void {
    Embeddings::fake();
    Anime::factory()->count(3)->withEmbedding()->create();

    $results = app(SearchAnimesByNaturalLanguageAction::class)->execute('action anime with robots');

    expect($results)->toBeInstanceOf(Collection::class)
        ->and($results->first())->toBeInstanceOf(Anime::class);
});

it('returns at most 10 results regardless of how many animes exist', function (): void {
    Embeddings::fake();
    Anime::factory()->count(15)->withEmbedding()->create();

    $results = app(SearchAnimesByNaturalLanguageAction::class)->execute('shonen fighting series');

    expect($results)->toHaveCount(10);
});

it('excludes animes that have no embedding', function (): void {
    Embeddings::fake();
    Anime::factory()->count(5)->withEmbedding()->create();
    Anime::factory()->count(5)->withoutEmbedding()->create();

    $results = app(SearchAnimesByNaturalLanguageAction::class)->execute('romance anime');

    expect($results)->toHaveCount(5);
});

it('respects a custom limit', function (): void {
    Embeddings::fake();
    Anime::factory()->count(10)->withEmbedding()->create();

    $results = app(SearchAnimesByNaturalLanguageAction::class)->execute('sports anime', limit: 3);

    expect($results)->toHaveCount(3);
});

it('generates an embedding using 1536 dimensions for the query', function (): void {
    Embeddings::fake();
    Anime::factory()->count(2)->withEmbedding()->create();

    app(SearchAnimesByNaturalLanguageAction::class)->execute('slice of life high school');

    Embeddings::assertGenerated(fn ($prompt) => $prompt->dimensions === 1536);
});

it('returns an empty collection when no anime has an embedding', function (): void {
    Embeddings::fake();
    Anime::factory()->count(5)->withoutEmbedding()->create();

    $results = app(SearchAnimesByNaturalLanguageAction::class)->execute('isekai adventure');

    expect($results)->toBeEmpty();
});
