<?php

declare(strict_types=1);

use App\DataTransferObjects\Payments\StripeWebhookPayloadDTO;
use App\Models\StripeWebhookEvent;
use App\Services\CreditCheckoutService;
use App\Services\CreditService;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('processes a webhook event only once when delivered multiple times', function (): void {
    $eventId = 'evt_test_'.str()->random(16);

    $dto = new StripeWebhookPayloadDTO(
        eventId: $eventId,
        eventType: 'customer.subscription.deleted',
        data: ['object' => ['customer' => 'cus_nonexistent']],
    );

    $service = new CreditCheckoutService(
        app(PaymentGatewayInterface::class),
        app(CreditService::class),
    );

    $service->processWebhookEvent($dto);
    $service->processWebhookEvent($dto);

    expect(StripeWebhookEvent::where('stripe_event_id', $eventId)->count())->toBe(1);
});
