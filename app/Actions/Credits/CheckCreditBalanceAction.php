<?php

declare(strict_types=1);

namespace App\Actions\Credits;

use App\Exceptions\InsufficientCreditsException;
use App\Models\User;

/**
 * Validates that a user has at least one credit available.
 *
 * Used as a fast pre-check before entering a DB transaction.
 * Does NOT apply a row-level lock — always re-check inside the transaction.
 */
final class CheckCreditBalanceAction
{
    /**
     * @throws InsufficientCreditsException
     */
    public function execute(User $user): void
    {
        if ($user->credit_balance < 1) {
            throw new InsufficientCreditsException;
        }
    }
}
