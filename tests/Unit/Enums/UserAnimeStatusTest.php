<?php

declare(strict_types=1);

use App\Enums\UserAnimeStatus;

it('label() returns the translation for each case', function (UserAnimeStatus $case) {
    expect($case->label())->toBe(__("enums.user_anime_status.{$case->value}"));
})->with(UserAnimeStatus::cases());

it('options() keys match enum values and values match translations', function () {
    $options = UserAnimeStatus::options();

    foreach (UserAnimeStatus::cases() as $case) {
        expect($options)->toHaveKey($case->value)
            ->and($options[$case->value])->toBe(__("enums.user_anime_status.{$case->value}"));
    }
});

it('options() contains one entry per case', function () {
    expect(UserAnimeStatus::options())->toHaveCount(count(UserAnimeStatus::cases()));
});
