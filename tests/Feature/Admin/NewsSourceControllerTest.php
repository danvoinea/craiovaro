<?php

namespace Tests\Feature\Admin;

use App\Models\NewsSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class NewsSourceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_news_sources(): void
    {
        NewsSource::factory()->count(2)->create();

        $response = $this->getJson('/api/admin/news-sources');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['id', 'name', 'base_url', 'keywords'],
                ],
            ]);
    }

    public function test_it_creates_news_source(): void
    {
        $payload = [
            'name' => 'Test Source',
            'base_url' => 'https://example.com/feed',
            'source_type' => 'rss',
            'selector_type' => 'css',
            'fetch_frequency' => 'hourly',
            'keywords' => ['Craiova', 'Dolj'],
            'is_active' => true,
        ];

        $response = $this->postJson(route('admin.newsSources.store'), $payload);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonPath('data.name', 'Test Source')
            ->assertJsonPath('data.base_url', 'https://example.com/feed')
            ->assertJsonPath('data.keywords', ['craiova', 'dolj']);

        $this->assertDatabaseHas('news_sources', [
            'name' => 'Test Source',
            'base_url' => 'https://example.com/feed',
            'keywords' => 'craiova, dolj',
        ]);
    }

    public function test_it_updates_news_source(): void
    {
        $source = NewsSource::factory()->create([
            'name' => 'Initial Source',
            'fetch_frequency' => 'hourly',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Updated Source',
            'fetch_frequency' => 'daily',
            'is_active' => false,
        ];

        $response = $this->patchJson(route('admin.newsSources.update', $source), $payload);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Source')
            ->assertJsonPath('data.fetch_frequency', 'daily')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('news_sources', [
            'id' => $source->id,
            'name' => 'Updated Source',
            'fetch_frequency' => 'daily',
            'is_active' => false,
        ]);
    }

    public function test_it_deletes_news_source(): void
    {
        $source = NewsSource::factory()->create();

        $response = $this->deleteJson(route('admin.newsSources.destroy', $source));

        $response->assertNoContent();

        $this->assertDatabaseMissing('news_sources', [
            'id' => $source->id,
        ]);
    }

    public function test_it_validates_link_selector_for_html_sources(): void
    {
        $payload = [
            'name' => 'HTML Source',
            'base_url' => 'https://example.com/news',
            'source_type' => 'html',
            'selector_type' => 'css',
            'fetch_frequency' => 'hourly',
        ];

        $response = $this->postJson(route('admin.newsSources.store'), $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('link_selector');
    }
}
