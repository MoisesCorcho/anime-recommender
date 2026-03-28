<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\Credits\CreditTopUpDTO;
use App\DataTransferObjects\Payments\CheckoutSessionDTO;
use App\DataTransferObjects\Payments\StripeWebhookPayloadDTO;
use App\Enums\CreditTransactionReason;
use App\Enums\SubscriptionTier;
use App\Models\StripeWebhookEvent;
use App\Models\User;
use App\Services\Payments\PaymentGatewayInterface;

/**
 * Handles checkout session creation and incoming Stripe webhook processing.
 *
 * Implements idempotency for webhook events via the stripe_webhook_events table.
 */
final class CreditCheckoutService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly CreditService $credits,
    ) {}

    public function createCheckoutSession(User $user, string $plan): CheckoutSessionDTO
    {
        $priceId = config("credits.prices.{$plan}", $plan);
        $mode = $plan === 'pro' ? 'subscription' : 'payment';

        return $this->gateway->createCheckoutSession(
            user: $user,
            priceId: $priceId,
            successUrl: route('checkout.success'),
            cancelUrl: route('checkout.cancel'),
            mode: $mode,
        );
    }

    public function processWebhookEvent(StripeWebhookPayloadDTO $dto): void
    {
        // Idempotency: create the event record only if not seen before.
        $event = StripeWebhookEvent::firstOrCreate(
            ['stripe_event_id' => $dto->eventId],
            [
                'event_type' => $dto->eventType,
                'payload' => $dto->data,
                'created_at' => now(),
            ],
        );

        if (! $event->wasRecentlyCreated) {
            return;
        }

        match ($dto->eventType) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($dto),
            'invoice.paid' => $this->handleInvoicePaid($dto),
            'customer.subscription.deleted' => $this->handleSubscriptionCanceled($dto),
            default => null,
        };

        $event->update(['processed_at' => now()]);
    }

    private function handleCheckoutCompleted(StripeWebhookPayloadDTO $dto): void
    {
        $sessionData = $dto->data['object'] ?? [];
        $customerId = $sessionData['customer'] ?? null;
        $mode = $sessionData['mode'] ?? null;
        $priceId = $sessionData['line_items']['data'][0]['price']['id'] ?? null;

        if ($customerId === null) {
            return;
        }

        $user = User::where('stripe_id', $customerId)->first();

        if ($user === null) {
            return;
        }

        if ($mode === 'subscription') {
            $user->subscription_tier = SubscriptionTier::Pro;
            $user->save();

            $this->credits->topUp($user, new CreditTopUpDTO(
                amount: (int) config('credits.pro_monthly_allowance', 5000),
                reason: CreditTransactionReason::SubscriptionRenewal,
                referenceId: $sessionData['payment_intent'] ?? null,
            ));
        } elseif ($mode === 'payment' && $priceId !== null) {
            $packPrices = array_diff_key((array) config('credits.prices'), ['pro' => null]);
            $planKey = array_search($priceId, $packPrices, strict: true);

            if ($planKey !== false) {
                $this->credits->topUp($user, new CreditTopUpDTO(
                    amount: (int) config("credits.pack_credits.{$planKey}"),
                    reason: CreditTransactionReason::PackPurchase,
                    referenceId: $sessionData['payment_intent'] ?? null,
                ));
            }
        }
    }

    private function handleInvoicePaid(StripeWebhookPayloadDTO $dto): void
    {
        $invoiceData = $dto->data['object'] ?? [];
        $customerId = $invoiceData['customer'] ?? null;

        if ($customerId === null) {
            return;
        }

        $user = User::where('stripe_id', $customerId)->first();

        if ($user === null) {
            return;
        }

        $this->credits->topUp($user, new CreditTopUpDTO(
            amount: (int) config('credits.pro_monthly_allowance', 5000),
            reason: CreditTransactionReason::SubscriptionRenewal,
            referenceId: $invoiceData['id'] ?? null,
        ));
    }

    private function handleSubscriptionCanceled(StripeWebhookPayloadDTO $dto): void
    {
        $subscriptionData = $dto->data['object'] ?? [];
        $customerId = $subscriptionData['customer'] ?? null;

        if ($customerId === null) {
            return;
        }

        $user = User::where('stripe_id', $customerId)->first();

        if ($user === null) {
            return;
        }

        $user->subscription_tier = SubscriptionTier::Free;
        $user->subscription_ends_at = null;
        $user->save();
    }
}
