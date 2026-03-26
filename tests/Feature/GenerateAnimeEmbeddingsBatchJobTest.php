<?php

declare(strict_types=1);

use App\Jobs\GenerateAnimeEmbeddingsBatchJob;
use App\Models\Anime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;

uses(RefreshDatabase::class);

it('generates and persists embeddings for all animes in the batch', function () {
    Embeddings::fake();

    $animes = Anime::factory()->count(3)->create();
    $ids = $animes->pluck('id')->all();

    dispatch(new GenerateAnimeEmbeddingsBatchJob($ids));

    foreach ($animes as $anime) {
        $fresh = $anime->fresh();
        expect($fresh->embedding)->toBeArray()->not->toBeEmpty();
    }

    Embeddings::assertGenerated(fn (EmbeddingsPrompt $prompt) => $prompt->dimensions === 1536);
});

it('sends one API call containing all texts in the batch', function () {
    Embeddings::fake();

    $animes = Anime::factory()->count(5)->create([
        'title' => 'Naruto',
        'type' => 'TV',
        'genres' => ['Action', 'Adventure'],
        'description' => 'A young ninja seeks recognition.',
    ]);

    $ids = $animes->pluck('id')->all();

    dispatch(new GenerateAnimeEmbeddingsBatchJob($ids));

    Embeddings::assertGenerated(function (EmbeddingsPrompt $prompt) {
        return $prompt->dimensions === 1536
            && $prompt->contains('Title: Naruto')
            && $prompt->contains('Format: TV')
            && $prompt->contains('Genres: Action, Adventure')
            && $prompt->contains('Synopsis: A young ninja seeks recognition.');
    });
});

it('returns early when all anime ids are missing from the database', function () {
    Embeddings::fake();

    dispatch(new GenerateAnimeEmbeddingsBatchJob(['non-existent-id']));

    Embeddings::assertNothingGenerated();
});

it('does not overwrite unrelated anime embeddings', function () {
    Embeddings::fake();

    $targetAnime = Anime::factory()->create();
    $untouchedAnime = Anime::factory()->withoutEmbedding()->create();

    dispatch(new GenerateAnimeEmbeddingsBatchJob([$targetAnime->id]));

    // Target should have received an embedding.
    expect($targetAnime->fresh()->embedding)->toBeArray()->not->toBeEmpty();

    // Untouched anime should remain without an embedding.
    expect($untouchedAnime->fresh()->embedding)->toBeNull();
});
