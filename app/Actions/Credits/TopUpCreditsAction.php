<?php

declare(strict_types=1);

namespace App\Actions\Credits;

use App\DataTransferObjects\Credits\CreditTopUpDTO;
use App\Enums\CreditTransactionType;
use App\Models\CreditTransaction;
use App\Models\User;

/**
 * Adds credits to a user's balance and records the ledger entry.
 */
final class TopUpCreditsAction
{
    public function execute(User $user, CreditTopUpDTO $dto): CreditTransaction
    {
        if ($dto->referenceId !== null) {
            $existing = CreditTransaction::where('user_id', $user->id)
                ->where('reference_id', $dto->referenceId)
                ->first();

            if ($existing !== null) {
                return $existing;
            }
        }

        $newBalance = $user->credit_balance + $dto->amount;

        $user->credit_balance = $newBalance;
        $user->save();

        return CreditTransaction::create([
            'user_id' => $user->id,
            'type' => CreditTransactionType::Credit->value,
            'amount' => $dto->amount,
            'reason' => $dto->reason->value,
            'reference_id' => $dto->referenceId,
            'balance_after' => $newBalance,
            'created_at' => now(),
        ]);
    }
}
