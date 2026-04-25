<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CreditTransactionReason;
use App\Enums\CreditTransactionType;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditTransaction>
 */
class CreditTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->numberBetween(1, 100);

        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(CreditTransactionType::cases())->value,
            'amount' => $amount,
            'reason' => fake()->randomElement(CreditTransactionReason::cases())->value,
            'reference_id' => null,
            'balance_after' => fake()->numberBetween(0, 1000),
            'created_at' => now(),
        ];
    }

    public function debit(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CreditTransactionType::Debit->value,
            'reason' => CreditTransactionReason::SemanticSearch->value,
            'amount' => 1,
        ]);
    }

    public function credit(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CreditTransactionType::Credit->value,
            'reason' => CreditTransactionReason::RegistrationBonus->value,
        ]);
    }
}
