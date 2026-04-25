<?php

declare(strict_types=1);

namespace App\Actions\Credits;

use App\DataTransferObjects\Credits\CreditTopUpDTO;
use App\Enums\CreditTransactionReason;
use App\Models\CreditTransaction;
use App\Models\User;

/**
 * Grants the registration bonus credits to a newly registered user.
 *
 * Reads the bonus amount from config('credits.registration_bonus'),
 * defaulting to 50 if the config key is absent.
 */
final class InitializeUserCreditsAction
{
    public function __construct(
        private readonly TopUpCreditsAction $topUp,
    ) {}

    public function execute(User $user): CreditTransaction
    {
        $bonus = (int) config('credits.registration_bonus', 50);

        return $this->topUp->execute($user, new CreditTopUpDTO(
            amount: $bonus,
            reason: CreditTransactionReason::RegistrationBonus,
        ));
    }
}
