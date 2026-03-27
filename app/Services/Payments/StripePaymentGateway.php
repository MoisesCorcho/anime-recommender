<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\DataTransferObjects\Payments\CheckoutSessionDTO;
use App\DataTransferObjects\Payments\SubscriptionDataDTO;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Cashier\Checkout;

/**
 * Stripe implementation of PaymentGatewayInterface.
 *
 * This is the only class that imports Stripe/Cashier types directly.
 * All other application code interacts with the payment system
 * through the interface and its DTOs.
 */
final class StripePaymentGateway implements PaymentGatewayInterface
{
    public function createCheckoutSession(
        User $user,
        string $priceId,
        string $successUrl,
        string $cancelUrl,
    ): CheckoutSessionDTO {
        $checkout = Checkout::create($user, [
            'mode' => 'subscription',
            'line_items' => [
                ['price' => $priceId, 'quantity' => 1],
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        $session = $checkout->asStripeCheckoutSession();

        return new CheckoutSessionDTO(
            sessionId: $session->id,
            checkoutUrl: $session->url,
        );
    }

    public function getActiveSubscription(User $user): ?SubscriptionDataDTO
    {
        $subscription = $user->subscription('default');

        if ($subscription === null || ! $subscription->active()) {
            return null;
        }

        $stripeSubscription = $subscription->asStripeSubscription();

        return new SubscriptionDataDTO(
            subscriptionId: $stripeSubscription->id,
            status: $stripeSubscription->status,
            currentPeriodEnd: Carbon::createFromTimestamp($stripeSubscription->current_period_end),
        );
    }

    public function cancelSubscription(User $user): bool
    {
        $subscription = $user->subscription('default');

        if ($subscription === null) {
            return false;
        }

        $subscription->cancel();

        return true;
    }
}
