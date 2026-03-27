<?php

declare(strict_types=1);

use App\Listeners\StripeEventListener;
use App\Models\StripeWebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Events\WebhookReceived;

uses(RefreshDatabase::class);

it('ignores stripe events that are not handled by the credit system', function (): void {
    $listener = app(StripeEventListener::class);

    $listener->handle(new WebhookReceived([
        'id' => 'evt_test_unhandled',
        'type' => 'payment_method.attached',
        'data' => [],
    ]));

    expect(StripeWebhookEvent::count())->toBe(0);
});
