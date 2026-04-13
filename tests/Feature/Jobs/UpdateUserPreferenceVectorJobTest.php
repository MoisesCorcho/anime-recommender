<?php

declare(strict_types=1);

use App\Jobs\UpdateUserPreferenceVectorJob;
use App\Models\Anime;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('absorbs 100% of the anime embedding on cold start without score', function (): void {
    $embedding = array_fill(0, 1536, 0.5);
    $anime = Anime::factory()->create(['embedding' => $embedding]);
    $user = User::factory()->create(['preference_vector' => null]);

    (new UpdateUserPreferenceVectorJob($user->id, $anime->id, null))->handle();

    $vector = json_decode(
        DB::table('users')->where('id', $user->id)->value('preference_vector'),
        true,
    );

    expect($vector[0])->toEqualWithDelta(0.5, 0.000001);
});

it('applies score weighting on cold start', function (): void {
    $embedding = array_fill(0, 1536, 1.0);
    $anime = Anime::factory()->create(['embedding' => $embedding]);
    $user = User::factory()->create(['preference_vector' => null]);

    (new UpdateUserPreferenceVectorJob($user->id, $anime->id, 5))->handle();

    $vector = json_decode(
        DB::table('users')->where('id', $user->id)->value('preference_vector'),
        true,
    );

    // score 5/10 = 0.5 weight → cold start: 1.0 * 0.5 = 0.5
    expect($vector[0])->toEqualWithDelta(0.5, 0.000001);
});

it('applies EMA formula when preference_vector already exists', function (): void {
    $oldEmbedding = array_fill(0, 1536, 1.0);
    $animeEmbedding = array_fill(0, 1536, 0.0);

    $anime = Anime::factory()->create(['embedding' => $animeEmbedding]);
    $user = User::factory()->create();

    DB::table('users')
        ->where('id', $user->id)
        ->update(['preference_vector' => json_encode($oldEmbedding)]);

    (new UpdateUserPreferenceVectorJob($user->id, $anime->id, null))->handle();

    $vector = json_decode(
        DB::table('users')->where('id', $user->id)->value('preference_vector'),
        true,
    );

    // alpha=0.8: (1.0 * 0.8) + (0.0 * 0.2 * 1.0) = 0.8
    expect($vector[0])->toEqualWithDelta(0.8, 0.000001);
});

it('applies EMA with score weighting on existing vector', function (): void {
    $oldEmbedding = array_fill(0, 1536, 1.0);
    $animeEmbedding = array_fill(0, 1536, 1.0);

    $anime = Anime::factory()->create(['embedding' => $animeEmbedding]);
    $user = User::factory()->create();

    DB::table('users')
        ->where('id', $user->id)
        ->update(['preference_vector' => json_encode($oldEmbedding)]);

    (new UpdateUserPreferenceVectorJob($user->id, $anime->id, 10))->handle();

    $vector = json_decode(
        DB::table('users')->where('id', $user->id)->value('preference_vector'),
        true,
    );

    // alpha=0.8, scoreWeight=1.0: (1.0 * 0.8) + (1.0 * 0.2 * 1.0) = 1.0
    expect($vector[0])->toEqualWithDelta(1.0, 0.000001);
});

it('returns early without updating when anime has no embedding', function (): void {
    $anime = Anime::factory()->withoutEmbedding()->create();
    $user = User::factory()->create(['preference_vector' => null]);

    (new UpdateUserPreferenceVectorJob($user->id, $anime->id, null))->handle();

    $fresh = DB::table('users')->where('id', $user->id)->value('preference_vector');

    expect($fresh)->toBeNull();
});

it('produces a vector with exactly 1536 dimensions', function (): void {
    $anime = Anime::factory()->withEmbedding()->create();
    $user = User::factory()->create(['preference_vector' => null]);

    (new UpdateUserPreferenceVectorJob($user->id, $anime->id, null))->handle();

    $vector = json_decode(
        DB::table('users')->where('id', $user->id)->value('preference_vector'),
        true,
    );

    expect(count($vector))->toBe(1536);
});

it('persists a non-null preference_vector after execution', function (): void {
    $anime = Anime::factory()->withEmbedding()->create();
    $user = User::factory()->create(['preference_vector' => null]);

    (new UpdateUserPreferenceVectorJob($user->id, $anime->id, null))->handle();

    $fresh = DB::table('users')->where('id', $user->id)->value('preference_vector');

    expect($fresh)->not->toBeNull();
});
