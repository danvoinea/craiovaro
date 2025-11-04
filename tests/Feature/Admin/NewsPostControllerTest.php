<?php

namespace Tests\Feature\Admin;

use App\Models\NewsPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class NewsPostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_news_posts(): void
    {
        NewsPost::factory()->count(3)->create();

        $response = $this->getJson(route('admin.newsPosts.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['id', 'title', 'slug', 'category_slug'],
                ],
            ]);
    }

    public function test_it_creates_news_post(): void
    {
        $payload = [
            'title' => 'Primăria lansează un nou proiect',
            'category_slug' => 'administratie',
            'category_label' => 'Administrație',
            'summary' => 'Un proiect important pentru oraș.',
            'body_html' => '<p>Detalii despre proiect.</p>',
            'published_at' => Carbon::now()->toIso8601String(),
            'hero_image_url' => 'https://example.com/image.jpg',
            'is_highlighted' => true,
        ];

        $response = $this->postJson(route('admin.newsPosts.store'), $payload);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonPath('data.title', 'Primăria lansează un nou proiect')
            ->assertJsonPath('data.slug', 'primaria-lanseaza-un-nou-proiect')
            ->assertJsonPath('data.category_slug', 'administratie')
            ->assertJsonPath('data.is_highlighted', true);

        $this->assertDatabaseHas('news_posts', [
            'title' => 'Primăria lansează un nou proiect',
            'category_slug' => 'administratie',
            'slug' => 'primaria-lanseaza-un-nou-proiect',
            'is_highlighted' => true,
        ]);
    }

    public function test_it_updates_news_post(): void
    {
        $post = NewsPost::factory()->create([
            'title' => 'Articol inițial',
            'category_slug' => 'evenimente',
            'slug' => 'articol-initial',
            'is_highlighted' => true,
        ]);

        $payload = [
            'title' => 'Articol actualizat',
            'slug' => 'articol-actualizat',
            'is_highlighted' => false,
        ];

        $response = $this->patchJson(route('admin.newsPosts.update', $post), $payload);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Articol actualizat')
            ->assertJsonPath('data.slug', 'articol-actualizat')
            ->assertJsonPath('data.is_highlighted', false);

        $this->assertDatabaseHas('news_posts', [
            'id' => $post->id,
            'title' => 'Articol actualizat',
            'slug' => 'articol-actualizat',
            'is_highlighted' => false,
        ]);
    }

    public function test_it_deletes_news_post(): void
    {
        $post = NewsPost::factory()->create();

        $response = $this->deleteJson(route('admin.newsPosts.destroy', $post));

        $response->assertNoContent();

        $this->assertDatabaseMissing('news_posts', [
            'id' => $post->id,
        ]);
    }

    public function test_it_validates_unique_slug_per_category(): void
    {
        NewsPost::factory()->create([
            'category_slug' => 'evenimente',
            'slug' => 'orasul-in-miscare',
        ]);

        $payload = [
            'title' => 'Orașul în mișcare',
            'category_slug' => 'evenimente',
            'slug' => 'orasul-in-miscare',
            'published_at' => Carbon::now()->toIso8601String(),
        ];

        $response = $this->postJson(route('admin.newsPosts.store'), $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('slug');
    }
}
