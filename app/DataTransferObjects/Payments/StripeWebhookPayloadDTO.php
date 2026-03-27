<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Payments;

/**
 * Parsed and verified Stripe webhook event payload.
 *
 * Created only after the signature has been verified by the gateway.
 */
final readonly class StripeWebhookPayloadDTO
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly string $eventId,
        public readonly string $eventType,
        public readonly array $data,
    ) {}
}
