<?php

declare(strict_types=1);

use App\Actions\Credits\TopUpCreditsAction;
use App\DataTransferObjects\Credits\CreditTopUpDTO;
use App\Enums\CreditTransactionReason;
use App\Enums\CreditTransactionType;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('adds credits to the user balance and returns a transaction', function (): void {
    $user = User::factory()->create(['credit_balance' => 10]);

    $dto = new CreditTopUpDTO(
        amount: 50,
        reason: CreditTransactionReason::RegistrationBonus,
    );

    $transaction = (new TopUpCreditsAction)->execute($user, $dto);

    expect($transaction)->toBeInstanceOf(CreditTransaction::class)
        ->and($transaction->type)->toBe(CreditTransactionType::Credit)
        ->and($transaction->amount)->toBe(50)
        ->and($transaction->reason)->toBe(CreditTransactionReason::RegistrationBonus)
        ->and($transaction->balance_after)->toBe(60);

    expect($user->fresh()->credit_balance)->toBe(60);
});

it('stores the reference_id when provided', function (): void {
    $user = User::factory()->create(['credit_balance' => 0]);

    $dto = new CreditTopUpDTO(
        amount: 100,
        reason: CreditTransactionReason::PackPurchase,
        referenceId: 'pi_test_abc123',
    );

    $transaction = (new TopUpCreditsAction)->execute($user, $dto);

    expect($transaction->reference_id)->toBe('pi_test_abc123');
});
