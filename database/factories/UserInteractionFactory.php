<?php

namespace Database\Factories;

use App\Enums\InteractionType;
use App\Models\User;
use App\Models\UserInteraction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserInteraction>
 */
class UserInteractionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(InteractionType::cases()),
            'payload' => ['query' => $this->faker->words(3, true)],
        ];
    }
}
