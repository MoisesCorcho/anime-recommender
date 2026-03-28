<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\DataTransferObjects\Payments\CheckoutSessionDTO;
use App\DataTransferObjects\Payments\SubscriptionDataDTO;
use App\Models\User;

interface PaymentGatewayInterface
{
    /**
     * @param  array<string, string>  $metadata
     */
    public function createCheckoutSession(
        User $user,
        string $priceId,
        string $successUrl,
        string $cancelUrl,
        string $mode = 'subscription',
        array $metadata = [],
    ): CheckoutSessionDTO;

    public function getActiveSubscription(User $user): ?SubscriptionDataDTO;

    public function cancelSubscription(User $user): bool;
}
