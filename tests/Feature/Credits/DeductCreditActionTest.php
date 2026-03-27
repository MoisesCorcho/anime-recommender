<?php

declare(strict_types=1);

use App\Actions\Credits\DeductCreditAction;
use App\DataTransferObjects\Credits\CreditDeductionResultDTO;
use App\Enums\CreditTransactionReason;
use App\Enums\CreditTransactionType;
use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deducts one credit and returns a result DTO', function (): void {
    $user = User::factory()->create(['credit_balance' => 5]);

    $result = (new DeductCreditAction)->execute($user);

    expect($result)->toBeInstanceOf(CreditDeductionResultDTO::class)
        ->and($result->balanceAfter)->toBe(4)
        ->and($result->transaction)->toBeInstanceOf(CreditTransaction::class)
        ->and($result->transaction->type)->toBe(CreditTransactionType::Debit)
        ->and($result->transaction->reason)->toBe(CreditTransactionReason::SemanticSearch)
        ->and($result->transaction->amount)->toBe(1);

    expect($user->fresh()->credit_balance)->toBe(4);
});

it('throws InsufficientCreditsException when balance is zero', function (): void {
    $user = User::factory()->create(['credit_balance' => 0]);

    expect(fn () => (new DeductCreditAction)->execute($user))
        ->toThrow(InsufficientCreditsException::class);
});

it('persists the transaction with correct balance_after snapshot', function (): void {
    $user = User::factory()->create(['credit_balance' => 3]);

    $result = (new DeductCreditAction)->execute($user);

    expect($result->transaction->balance_after)->toBe(2);
});
