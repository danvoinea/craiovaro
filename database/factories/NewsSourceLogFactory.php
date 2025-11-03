<?php

namespace Database\Factories;

use App\Models\NewsSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsSourceLog>
 */
class NewsSourceLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'news_source_id' => NewsSource::factory(),
            'status' => $this->faker->randomElement(['success', 'skipped', 'error']),
            'message' => $this->faker->sentence(),
            'ran_at' => now(),
            'duration_ms' => $this->faker->numberBetween(100, 2000),
            'context' => ['articles' => $this->faker->numberBetween(0, 10)],
        ];
    }
}
