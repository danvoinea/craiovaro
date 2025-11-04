<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsPost>
 */
class NewsPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(6);
        $category = $this->faker->randomElement(['craiova', 'dolj', 'evenimente', 'opinie']);
        $summary = $this->faker->paragraph();

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'category_slug' => Str::slug($category),
            'category_label' => Str::title($category),
            'summary' => $summary,
            'body_html' => '<p>'.implode('</p><p>', $this->faker->paragraphs(3)).'</p>',
            'body_text' => $summary.' '.$this->faker->paragraph(),
            'hero_image_url' => $this->faker->imageUrl(1200, 630, 'news', false),
            'published_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'is_highlighted' => $this->faker->boolean(70),
            'is_published' => true,
        ];
    }
}
