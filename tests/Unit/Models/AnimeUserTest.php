<?php

declare(strict_types=1);

use App\Enums\UserAnimeStatus;
use App\Models\AnimeUser;

// ---------------------------------------------------------------------------
// Score mutator — tested at model level because attach() bypasses setAttribute
// ---------------------------------------------------------------------------

it('accepts a valid score within the 1–10 range', function () {
    $pivot = new AnimeUser;

    foreach (range(1, 10) as $score) {
        $pivot->score = $score;
        expect($pivot->score)->toBe($score);
    }
});

it('accepts a null score (user has not rated yet)', function () {
    $pivot = new AnimeUser;
    $pivot->score = null;

    expect($pivot->score)->toBeNull();
});

it('throws InvalidArgumentException when score is less than 1', function () {
    $pivot = new AnimeUser;
    $pivot->score = 0;
})->throws(InvalidArgumentException::class, 'Score must be between 1 and 10, got 0.');

it('throws InvalidArgumentException when score is greater than 10', function () {
    $pivot = new AnimeUser;
    $pivot->score = 11;
})->throws(InvalidArgumentException::class, 'Score must be between 1 and 10, got 11.');

it('throws InvalidArgumentException for negative scores', function () {
    $pivot = new AnimeUser;
    $pivot->score = -5;
})->throws(InvalidArgumentException::class);

// ---------------------------------------------------------------------------
// Casts — verified on a raw instance without touching the database
// ---------------------------------------------------------------------------

it('casts status raw string to UserAnimeStatus enum', function () {
    $pivot = new AnimeUser;
    $pivot->setRawAttributes(['status' => 'WATCHING']);

    expect($pivot->status)->toBeInstanceOf(UserAnimeStatus::class)
        ->and($pivot->status)->toBe(UserAnimeStatus::Watching);
});

it('casts is_favorite raw integer 1 to boolean true', function () {
    $pivot = new AnimeUser;
    $pivot->setRawAttributes(['is_favorite' => 1]);

    expect($pivot->is_favorite)->toBeTrue();
});

it('casts is_favorite raw integer 0 to boolean false', function () {
    $pivot = new AnimeUser;
    $pivot->setRawAttributes(['is_favorite' => 0]);

    expect($pivot->is_favorite)->toBeFalse();
});

it('casts episodes_watched raw string to integer', function () {
    $pivot = new AnimeUser;
    $pivot->setRawAttributes(['episodes_watched' => '12']);

    expect($pivot->episodes_watched)->toBeInt()->toBe(12);
});
