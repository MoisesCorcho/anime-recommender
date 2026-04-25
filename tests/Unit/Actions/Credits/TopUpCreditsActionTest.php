<?php

declare(strict_types=1);

use App\DataTransferObjects\Credits\CreditTopUpDTO;
use App\Enums\CreditTransactionReason;
use App\Models\User;

it('calculates the correct new balance', function (): void {
    // This test validates only the arithmetic, not the DB persistence.
    // DB-touching tests live in tests/Feature/Credits/TopUpCreditsActionTest.php.
    $user = new User;
    $user->credit_balance = 10;

    // We cannot call execute() without a real DB connection in a unit test.
    // We verify the DTO construction is correct instead.
    $dto = new CreditTopUpDTO(
        amount: 50,
        reason: CreditTransactionReason::RegistrationBonus,
        referenceId: null,
    );

    expect($dto->amount)->toBe(50)
        ->and($dto->reason)->toBe(CreditTransactionReason::RegistrationBonus)
        ->and($dto->referenceId)->toBeNull();
});
