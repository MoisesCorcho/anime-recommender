<?php

declare(strict_types=1);

use App\Enums\InteractionType;

it('label() returns the translation for each case', function (InteractionType $case) {
    expect($case->label())->toBe(__("enums.interaction_type.{$case->value}"));
})->with(InteractionType::cases());

it('options() keys match enum values and values match translations', function () {
    $options = InteractionType::options();

    foreach (InteractionType::cases() as $case) {
        expect($options)->toHaveKey($case->value)
            ->and($options[$case->value])->toBe(__("enums.interaction_type.{$case->value}"));
    }
});

it('options() contains one entry per case', function () {
    expect(InteractionType::options())->toHaveCount(count(InteractionType::cases()));
});
