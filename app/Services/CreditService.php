<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Credits\CheckCreditBalanceAction;
use App\Actions\Credits\DeductCreditAction;
use App\Actions\Credits\InitializeUserCreditsAction;
use App\Actions\Credits\TopUpCreditsAction;
use App\DataTransferObjects\Credits\CreditDeductionResultDTO;
use App\DataTransferObjects\Credits\CreditTopUpDTO;
use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates all credit-related operations.
 *
 * Coordinates balance checks, deductions, and top-ups while ensuring
 * race-condition safety through database-level locking.
 */
final class CreditService
{
    public function __construct(
        private readonly DeductCreditAction $deduct,
        private readonly TopUpCreditsAction $topUp,
        private readonly InitializeUserCreditsAction $initialize,
        private readonly CheckCreditBalanceAction $checkBalance,
    ) {}

    /**
     * Deducts one credit for a semantic search operation.
     *
     * NOTE: The OpenAI embedding call must happen BEFORE this method is
     * invoked. This service is responsible only for the credit ledger.
     *
     * @throws InsufficientCreditsException
     */
    public function deductForSemanticSearch(User $user): CreditDeductionResultDTO
    {
        // Fast pre-check without a lock to fail early on obvious cases.
        $this->checkBalance->execute($user);

        return DB::transaction(function () use ($user): CreditDeductionResultDTO {
            /** @var User $lockedUser */
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            // Re-check inside the transaction with the locked row.
            $this->checkBalance->execute($lockedUser);

            return $this->deduct->execute($lockedUser);
        });
    }

    public function topUp(User $user, CreditTopUpDTO $dto): CreditTransaction
    {
        return $this->topUp->execute($user, $dto);
    }

    public function initializeRegistrationBonus(User $user): CreditTransaction
    {
        return $this->initialize->execute($user);
    }

    public function getBalance(User $user): int
    {
        return $user->credit_balance;
    }
}
