<?php

declare(strict_types=1);

use App\Actions\Credits\DeductCreditAction;
use App\Exceptions\InsufficientCreditsException;
use App\Models\User;

it('throws InsufficientCreditsException when balance is zero', function (): void {
    $user = new User;
    $user->credit_balance = 0;

    expect(fn () => (new DeductCreditAction)->execute($user))
        ->toThrow(InsufficientCreditsException::class);
});
