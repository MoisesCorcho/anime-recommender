<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Payments;

use Carbon\Carbon;

/**
 * Normalized view of a Stripe subscription.
 *
 * Insulates the domain from Stripe SDK subscription objects.
 */
final readonly class SubscriptionDataDTO
{
    public function __construct(
        public readonly string $subscriptionId,
        public readonly string $status,
        public readonly Carbon $currentPeriodEnd,
    ) {}
}
