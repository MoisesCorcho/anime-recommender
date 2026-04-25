<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Payments;

/**
 * Stripe Checkout Session result.
 *
 * Wraps the session ID and redirect URL returned by the payment gateway,
 * decoupling the rest of the application from Stripe's SDK types.
 */
final readonly class CheckoutSessionDTO
{
    public function __construct(
        public readonly string $sessionId,
        public readonly string $checkoutUrl,
    ) {}
}
