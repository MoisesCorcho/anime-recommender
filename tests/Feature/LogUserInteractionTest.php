<?php

declare(strict_types=1);

use App\Enums\InteractionType;
use App\Jobs\LogUserInteractionJob;
use App\Models\User;
use App\Models\UserInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists an authenticated user interaction to the database', function () {
    $user = User::factory()->create();
    $payload = InteractionType::semanticSearchPayload('attack on titan', 5);

    dispatch(new LogUserInteractionJob(
        user: $user,
        type: InteractionType::SemanticSearch,
        payload: $payload,
    ));

    expect(UserInteraction::count())->toBe(1);

    $interaction = UserInteraction::first();

    expect($interaction->user_id)->toBe($user->id)
        ->and($interaction->type)->toBe(InteractionType::SemanticSearch)
        ->and($interaction->payload)->toBe($payload);
});

it('persists a guest interaction with null user_id', function () {
    $payload = InteractionType::animeViewPayload('some-ulid');

    dispatch(new LogUserInteractionJob(
        user: null,
        type: InteractionType::AnimeView,
        payload: $payload,
    ));

    $this->assertDatabaseHas('user_interactions', [
        'user_id' => null,
        'type' => InteractionType::AnimeView->value,
    ]);

    $interaction = UserInteraction::first();

    expect($interaction->payload)->toBe($payload);
});

it('casts the type column to the InteractionType enum', function () {
    UserInteraction::factory()->create(['type' => InteractionType::FavoriteAdd]);

    $interaction = UserInteraction::first();

    expect($interaction->type)->toBeInstanceOf(InteractionType::class)
        ->and($interaction->type)->toBe(InteractionType::FavoriteAdd);
});
