<?php

namespace Database\Factories;

use App\Models\NewsSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsRaw>
 */
class NewsRawFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $articleUrl = $this->faker->unique()->url();

        return [
            'news_source_id' => NewsSource::factory(),
            'title' => $this->faker->sentence(),
            'body_html' => '<p>'.$this->faker->paragraph(3).'</p>',
            'body_text' => $this->faker->paragraph(3),
            'published_at' => $this->faker->dateTimeBetween('-7 days'),
            'source_name' => $this->faker->company().' News',
            'source_url' => $articleUrl,
            'cover_image_url' => $this->faker->imageUrl(),
            'url_hash' => hash('sha256', $articleUrl),
            'meta' => [],
        ];
    }
}
