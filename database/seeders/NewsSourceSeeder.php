<?php

namespace Database\Seeders;

use App\Models\NewsSource;
use Illuminate\Database\Seeder;

class NewsSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'name' => 'Gazeta de Sud',
                'base_url' => 'https://www.gds.ro/feed/',
                'source_type' => 'rss',
                'fetch_frequency' => 'hourly',
                'is_active' => true,
            ],
            [
                'name' => 'Cuvântul Libertății',
                'base_url' => 'https://cvlpress.ro/feed/',
                'source_type' => 'rss',
                'fetch_frequency' => 'hourly',
                'is_active' => true,
            ],
            [
                'name' => 'Știri Craiova',
                'base_url' => 'https://stiricraiova.ro/feed/',
                'source_type' => 'rss',
                'fetch_frequency' => 'hourly',
                'is_active' => true,
            ],
            [
                'name' => 'IPJ Dolj',
                'base_url' => 'https://dj.politiaromana.ro/ro/stiri-si-media/stiri',
                'source_type' => 'html',
                'fetch_frequency' => 'daily',
                'is_active' => false,
                'link_selector' => '.article-list a',
                'title_selector' => '.article-title',
                'body_selector' => '.article-content',
                'date_selector' => '.article-date',
            ],
            [
                'name' => 'Jurnalul Olteniei',
                'base_url' => 'https://www.jurnalulolteniei.ro/feed/',
                'source_type' => 'rss',
                'fetch_frequency' => 'hourly',
                'is_active' => true,
            ],
            [
                'name' => 'Ediție Specială',
                'base_url' => 'https://www.editie.ro/feed/',
                'source_type' => 'rss',
                'fetch_frequency' => 'daily',
                'is_active' => true,
            ],
            [
                'name' => 'TVR Craiova',
                'base_url' => 'https://tvr-craiova.ro/feed/',
                'source_type' => 'rss',
                'fetch_frequency' => 'hourly',
                'is_active' => true,
            ],
            [
                'name' => 'Jurnal de Craiova',
                'base_url' => 'https://jurnaldecraiova.ro/feed/',
                'source_type' => 'rss',
                'fetch_frequency' => 'hourly',
                'is_active' => true,
            ],
            [
                'name' => 'Craiova Forum',
                'base_url' => 'https://www.craiovaforum.ro/feed/',
                'source_type' => 'rss',
                'fetch_frequency' => 'daily',
                'is_active' => false,
            ],
        ];

        foreach ($sources as $attributes) {
            NewsSource::query()->firstOrCreate(
                ['base_url' => $attributes['base_url']],
                array_merge([
                    'selector_type' => 'css',
                    'title_selector' => null,
                    'body_selector' => null,
                    'date_selector' => null,
                    'image_selector' => null,
                    'link_selector' => $attributes['source_type'] === 'html' ? ($attributes['link_selector'] ?? null) : null,
                    'keywords' => 'craiova, dolj',
                    'is_active' => $attributes['is_active'] ?? true,
                    'fetch_frequency' => $attributes['fetch_frequency'] ?? 'hourly',
                ], $attributes)
            );
        }
    }
}
