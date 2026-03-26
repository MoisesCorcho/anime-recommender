<?php

declare(strict_types=1);

use App\Enums\UserAnimeStatus;

it('returns a human-readable label for each case', function (UserAnimeStatus $case, string $expected) {
    expect($case->label())->toBe($expected);
})->with([
    [UserAnimeStatus::Watching,    'Watching'],
    [UserAnimeStatus::Completed,   'Completed'],
    [UserAnimeStatus::OnHold,      'On Hold'],
    [UserAnimeStatus::Dropped,     'Dropped'],
    [UserAnimeStatus::PlanToWatch, 'Plan to Watch'],
    [UserAnimeStatus::Blacklisted, 'Blacklisted'],
]);

it('returns all cases as a value => label map via options()', function () {
    $options = UserAnimeStatus::options();

    expect($options)->toBe([
        'WATCHING' => 'Watching',
        'COMPLETED' => 'Completed',
        'ON_HOLD' => 'On Hold',
        'DROPPED' => 'Dropped',
        'PLAN_TO_WATCH' => 'Plan to Watch',
        'BLACKLISTED' => 'Blacklisted',
    ]);
});

it('options() contains one entry per case', function () {
    expect(UserAnimeStatus::options())->toHaveCount(count(UserAnimeStatus::cases()));
});
