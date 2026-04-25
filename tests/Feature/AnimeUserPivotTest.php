<?php

declare(strict_types=1);

use App\Enums\UserAnimeStatus;
use App\Models\Anime;
use App\Models\AnimeUser;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Attach an anime to a user with sensible defaults and return the pivot model.
 */
function attachAnime(User $user, Anime $anime, array $pivotData = []): AnimeUser
{
    $user->animes()->attach($anime->id, array_merge([
        'status' => UserAnimeStatus::Watching->value,
    ], $pivotData));

    /** @var AnimeUser $pivot */
    $pivot = $user->animes()->wherePivot('anime_id', $anime->id)->first()->pivot;

    return $pivot;
}

// ---------------------------------------------------------------------------
// Relationship integrity
// ---------------------------------------------------------------------------

it('attaches an anime to a user and stores it in the pivot table', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::Watching->value]);

    $this->assertDatabaseHas('anime_user', [
        'user_id' => $user->id,
        'anime_id' => $anime->id,
        'status' => UserAnimeStatus::Watching->value,
    ]);
});

it('a user can track many animes simultaneously', function () {
    $user = User::factory()->create();
    $animes = Anime::factory()->count(3)->create();

    foreach ($animes as $anime) {
        $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::PlanToWatch->value]);
    }

    expect($user->animes()->count())->toBe(3);
});

it('an anime can be tracked by many users simultaneously', function () {
    $anime = Anime::factory()->create();
    $users = User::factory()->count(4)->create();

    foreach ($users as $user) {
        $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::Completed->value]);
    }

    expect($anime->users()->count())->toBe(4);
});

// ---------------------------------------------------------------------------
// Pivot casts via relationship
// ---------------------------------------------------------------------------

it('casts status to UserAnimeStatus enum when loaded through the relationship', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $pivot = attachAnime($user, $anime, ['status' => UserAnimeStatus::Completed->value]);

    expect($pivot->status)->toBeInstanceOf(UserAnimeStatus::class)
        ->and($pivot->status)->toBe(UserAnimeStatus::Completed);
});

it('casts is_favorite to boolean when loaded through the relationship', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $pivot = attachAnime($user, $anime, ['is_favorite' => true]);

    expect($pivot->is_favorite)->toBeTrue();
});

it('casts score to integer when loaded through the relationship', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $pivot = attachAnime($user, $anime, ['score' => 8]);

    expect($pivot->score)->toBeInt()->toBe(8);
});

it('casts episodes_watched to integer when loaded through the relationship', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $pivot = attachAnime($user, $anime, ['episodes_watched' => 24]);

    expect($pivot->episodes_watched)->toBeInt()->toBe(24);
});

it('returns an AnimeUser pivot instance through the relationship', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $pivot = attachAnime($user, $anime);

    expect($pivot)->toBeInstanceOf(AnimeUser::class);
});

// ---------------------------------------------------------------------------
// Column defaults
// ---------------------------------------------------------------------------

it('defaults is_favorite to false when not provided', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $pivot = attachAnime($user, $anime);

    expect($pivot->is_favorite)->toBeFalse();
});

it('defaults episodes_watched to 0 when not provided', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $pivot = attachAnime($user, $anime);

    expect($pivot->episodes_watched)->toBe(0);
});

it('stores null score when the user has not rated the anime', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $pivot = attachAnime($user, $anime);

    expect($pivot->score)->toBeNull();
});

// ---------------------------------------------------------------------------
// Status coverage
// ---------------------------------------------------------------------------

it('stores every UserAnimeStatus case correctly', function (UserAnimeStatus $status) {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $pivot = attachAnime($user, $anime, ['status' => $status->value]);

    expect($pivot->status)->toBe($status);
})->with(UserAnimeStatus::cases());

// ---------------------------------------------------------------------------
// Unique constraint
// ---------------------------------------------------------------------------

it('prevents the same user from tracking the same anime twice', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::Watching->value]);

    expect(fn () => $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::Completed->value]))
        ->toThrow(UniqueConstraintViolationException::class);
});

// ---------------------------------------------------------------------------
// Cascade delete
// ---------------------------------------------------------------------------

it('removes the pivot row when the user is deleted', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::Watching->value]);

    $user->delete();

    $this->assertDatabaseMissing('anime_user', ['user_id' => $user->id]);
});

it('removes the pivot row when the anime is deleted', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::Watching->value]);

    $anime->delete();

    $this->assertDatabaseMissing('anime_user', ['anime_id' => $anime->id]);
});

// ---------------------------------------------------------------------------
// Update existing pivot
// ---------------------------------------------------------------------------

it('can update the status of a tracked anime', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $user->animes()->attach($anime->id, ['status' => UserAnimeStatus::Watching->value]);
    $user->animes()->updateExistingPivot($anime->id, ['status' => UserAnimeStatus::Completed->value]);

    $this->assertDatabaseHas('anime_user', [
        'user_id' => $user->id,
        'anime_id' => $anime->id,
        'status' => UserAnimeStatus::Completed->value,
    ]);
});

it('can mark an anime as favourite independently of its status', function () {
    $user = User::factory()->create();
    $anime = Anime::factory()->create();

    $user->animes()->attach($anime->id, [
        'status' => UserAnimeStatus::Dropped->value,
        'is_favorite' => true,
    ]);

    /** @var AnimeUser $pivot */
    $pivot = $user->animes()->wherePivot('anime_id', $anime->id)->first()->pivot;

    expect($pivot->status)->toBe(UserAnimeStatus::Dropped)
        ->and($pivot->is_favorite)->toBeTrue();
});
