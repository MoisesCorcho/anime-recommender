<?php

declare(strict_types=1);

use App\Enums\UserAnimeStatus;
use App\Jobs\UpdateUserPreferenceVectorJob;
use App\Models\Anime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Triggers that SHOULD dispatch the job
// ---------------------------------------------------------------------------

it('dispatches the job when pivot is created with COMPLETED status', function (): void {
    Queue::fake();
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::Completed->value]);

    Queue::assertPushed(UpdateUserPreferenceVectorJob::class);
});

it('dispatches the job when pivot is created with is_favorite = true', function (): void {
    Queue::fake();
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $user->animes()->attach($anime->id, [
        'status' => UserAnimeStatus::Watching->value,
        'is_favorite' => true,
    ]);

    Queue::assertPushed(UpdateUserPreferenceVectorJob::class);
});

it('dispatches the job when pivot is created with a score', function (): void {
    Queue::fake();
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $user->animes()->attach($anime->id, [
        'status' => UserAnimeStatus::Watching->value,
        'score' => 8,
    ]);

    Queue::assertPushed(UpdateUserPreferenceVectorJob::class);
});

it('dispatches the job when status changes to COMPLETED', function (): void {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();
    $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::Watching->value]);

    Queue::fake();

    $user->animes()->updateExistingPivot($anime->id, [
        'status' => UserAnimeStatus::Completed->value,
    ]);

    Queue::assertPushed(UpdateUserPreferenceVectorJob::class);
});

it('dispatches the job when is_favorite changes to true', function (): void {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();
    $user->animes()->attach($anime->id, [
        'status' => UserAnimeStatus::Watching->value,
        'is_favorite' => false,
    ]);

    Queue::fake();

    $user->animes()->updateExistingPivot($anime->id, ['is_favorite' => true]);

    Queue::assertPushed(UpdateUserPreferenceVectorJob::class);
});

it('dispatches the job when score is set to a non-null value', function (): void {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();
    $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::Watching->value]);

    Queue::fake();

    $user->animes()->updateExistingPivot($anime->id, ['score' => 7]);

    Queue::assertPushed(UpdateUserPreferenceVectorJob::class);
});

it('passes the correct userId, animeId, and score to the job', function (): void {
    Queue::fake();
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $user->animes()->attach($anime->id, [
        'status' => UserAnimeStatus::Completed->value,
        'score' => 9,
    ]);

    Queue::assertPushed(
        UpdateUserPreferenceVectorJob::class,
        fn (UpdateUserPreferenceVectorJob $job): bool => $job->userId === $user->id
            && $job->animeId === $anime->id
            && $job->score === 9,
    );
});

// ---------------------------------------------------------------------------
// Triggers that SHOULD NOT dispatch the job
// ---------------------------------------------------------------------------

it('does not dispatch the job when pivot is created with WATCHING status only', function (): void {
    Queue::fake();
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::Watching->value]);

    Queue::assertNotPushed(UpdateUserPreferenceVectorJob::class);
});

it('does not dispatch the job when pivot is created with PLAN_TO_WATCH status only', function (): void {
    Queue::fake();
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::PlanToWatch->value]);

    Queue::assertNotPushed(UpdateUserPreferenceVectorJob::class);
});

it('does not dispatch the job when is_favorite changes to false', function (): void {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();
    $user->animes()->attach($anime->id, [
        'status' => UserAnimeStatus::Watching->value,
        'is_favorite' => false,
    ]);

    Queue::fake();

    $user->animes()->updateExistingPivot($anime->id, ['is_favorite' => false]);

    Queue::assertNotPushed(UpdateUserPreferenceVectorJob::class);
});

it('does not dispatch the job when status changes to DROPPED', function (): void {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();
    $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::Watching->value]);

    Queue::fake();

    $user->animes()->updateExistingPivot($anime->id, [
        'status' => UserAnimeStatus::Dropped->value,
    ]);

    Queue::assertNotPushed(UpdateUserPreferenceVectorJob::class);
});

it('does not dispatch the job when only episodes_watched changes', function (): void {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();
    $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::Watching->value]);

    Queue::fake();

    $user->animes()->updateExistingPivot($anime->id, ['episodes_watched' => 5]);

    Queue::assertNotPushed(UpdateUserPreferenceVectorJob::class);
});
