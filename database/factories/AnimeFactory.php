<?php

namespace Database\Factories;

use App\Models\Anime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Anime>
 */
class AnimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mal_id' => $this->faker->unique()->numberBetween(1, 999_999),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'image_url' => $this->faker->imageUrl(),
            'type' => $this->faker->randomElement(['TV', 'Movie', 'OVA', 'ONA']),
            'episodes' => $this->faker->numberBetween(1, 100),
            'status' => 'Finished Airing',
            'released_year' => $this->faker->numberBetween(1990, 2024),
            'genres' => $this->faker->randomElements(['Action', 'Drama', 'Fantasy', 'Sci-Fi', 'Comedy'], 2),
            'score' => $this->faker->randomFloat(2, 1, 10),
            'embedding' => null,
        ];
    }

    /**
     * State for an anime without an embedding (default, ready for generation).
     */
    public function withoutEmbedding(): static
    {
        return $this->state(['embedding' => null]);
    }
}
