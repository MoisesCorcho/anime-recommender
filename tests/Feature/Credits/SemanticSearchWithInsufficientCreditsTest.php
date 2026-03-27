<?php

declare(strict_types=1);

use App\Exceptions\InsufficientCreditsException;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('throws InsufficientCreditsException when user has no credits', function (): void {
    $user = User::factory()->create(['credit_balance' => 0]);

    $service = app(CreditService::class);

    expect(fn () => $service->deductForSemanticSearch($user))
        ->toThrow(InsufficientCreditsException::class, 'Insufficient credits.');
});

it('does not create a transaction when balance is insufficient', function (): void {
    $user = User::factory()->create(['credit_balance' => 0]);

    $service = app(CreditService::class);

    try {
        $service->deductForSemanticSearch($user);
    } catch (InsufficientCreditsException) {
        // expected
    }

    expect($user->creditTransactions()->count())->toBe(0);
    expect($user->fresh()->credit_balance)->toBe(0);
});
