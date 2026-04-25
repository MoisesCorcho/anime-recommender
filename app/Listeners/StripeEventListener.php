<?php

declare(strict_types=1);

namespace App\Listeners;

use App\DataTransferObjects\Payments\StripeWebhookPayloadDTO;
use App\Services\CreditCheckoutService;
use Laravel\Cashier\Events\WebhookReceived;

final class StripeEventListener
{
    private const array HANDLED_EVENTS = [
        'checkout.session.completed',
        'invoice.paid',
        'customer.subscription.deleted',
    ];

    public function __construct(
        private readonly CreditCheckoutService $checkout,
    ) {}

    public function handle(WebhookReceived $event): void
    {
        if (! in_array($event->payload['type'], self::HANDLED_EVENTS, strict: true)) {
            return;
        }

        $dto = new StripeWebhookPayloadDTO(
            eventId: $event->payload['id'],
            eventType: $event->payload['type'],
            data: $event->payload['data'] ?? [],
        );

        $this->checkout->processWebhookEvent($dto);
    }
}
