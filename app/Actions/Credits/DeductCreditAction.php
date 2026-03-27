<?php

declare(strict_types=1);

namespace App\Actions\Credits;

use App\DataTransferObjects\Credits\CreditDeductionResultDTO;
use App\Enums\CreditTransactionReason;
use App\Enums\CreditTransactionType;
use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditTransaction;
use App\Models\User;

/**
 * Deducts one credit from the user's balance and records the ledger entry.
 *
 * MUST always be called inside a DB::transaction() with lockForUpdate()
 * already applied on the user row. Never call this method directly without
 * the surrounding transaction — race conditions will corrupt the balance.
 */
final class DeductCreditAction
{
    /**
     * @throws InsufficientCreditsException
     */
    public function execute(User $user): CreditDeductionResultDTO
    {
        if ($user->credit_balance < 1) {
            throw new InsufficientCreditsException;
        }

        $newBalance = $user->credit_balance - 1;

        $user->credit_balance = $newBalance;
        $user->save();

        $transaction = CreditTransaction::create([
            'user_id' => $user->id,
            'type' => CreditTransactionType::Debit->value,
            'amount' => 1,
            'reason' => CreditTransactionReason::SemanticSearch->value,
            'reference_id' => null,
            'balance_after' => $newBalance,
            'created_at' => now(),
        ]);

        return new CreditDeductionResultDTO(
            balanceAfter: $newBalance,
            transaction: $transaction,
        );
    }
}
