<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShortLink>
 */
class ShortLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'news_raw_id' => null,
            'code' => $this->faker->unique()->lexify('??????'),
            'target_url' => $this->faker->url(),
            'click_count' => 0,
            'last_clicked_at' => null,
        ];
    }
}
