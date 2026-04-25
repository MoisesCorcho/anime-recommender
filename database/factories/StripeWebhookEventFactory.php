<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StripeWebhookEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StripeWebhookEvent>
 */
class StripeWebhookEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stripe_event_id' => 'evt_'.fake()->regexify('[a-zA-Z0-9]{24}'),
            'event_type' => 'checkout.session.completed',
            'payload' => [],
            'processed_at' => null,
            'created_at' => now(),
        ];
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'processed_at' => now(),
        ]);
    }
}
