<?php

declare(strict_types=1);

use App\Actions\Credits\CheckCreditBalanceAction;
use App\Exceptions\InsufficientCreditsException;
use App\Models\User;

it('does not throw when the user has credits', function (): void {
    $user = new User;
    $user->credit_balance = 10;

    expect(fn () => (new CheckCreditBalanceAction)->execute($user))->not->toThrow(InsufficientCreditsException::class);
});

it('throws InsufficientCreditsException when balance is zero', function (): void {
    $user = new User;
    $user->credit_balance = 0;

    expect(fn () => (new CheckCreditBalanceAction)->execute($user))->toThrow(InsufficientCreditsException::class);
});

it('throws InsufficientCreditsException when balance is exactly one below threshold', function (): void {
    $user = new User;
    $user->credit_balance = 0;

    expect(fn () => (new CheckCreditBalanceAction)->execute($user))
        ->toThrow(InsufficientCreditsException::class, 'Insufficient credits.');
});
