<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Credits;

use App\Enums\CreditTransactionReason;

/**
 * Input data required to top up a user's credit balance.
 */
final readonly class CreditTopUpDTO
{
    public function __construct(
        public readonly int $amount,
        public readonly CreditTransactionReason $reason,
        public readonly ?string $referenceId = null,
    ) {}
}
