<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Credits;

use App\Models\CreditTransaction;

/**
 * Carries the result of a credit deduction operation.
 *
 * Bundles the updated balance and the transaction record together
 * so callers can respond without an extra database round-trip.
 */
final readonly class CreditDeductionResultDTO
{
    public function __construct(
        public readonly int $balanceAfter,
        public readonly CreditTransaction $transaction,
    ) {}
}
