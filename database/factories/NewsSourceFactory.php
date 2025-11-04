<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsSource>
 */
class NewsSourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company().' News',
            'base_url' => $this->faker->url(),
            'source_type' => $this->faker->randomElement(['rss', 'html']),
            'selector_type' => 'css',
            'title_selector' => 'h1',
            'body_selector' => 'article',
            'date_selector' => 'time',
            'image_selector' => 'img.featured',
            'link_selector' => 'a.article-link',
            'fetch_frequency' => $this->faker->randomElement(['5m', '15m', 'hourly', 'daily']),
            'keywords' => 'craiova, dolj',
            'is_active' => true,
        ];
    }
}
