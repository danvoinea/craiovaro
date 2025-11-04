<?php

namespace Tests\Feature\News;

use App\Models\NewsPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NewsPostPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_highlighted_posts_appear_on_homepage(): void
    {
        $post = NewsPost::factory()->create([
            'title' => 'Curated Story Craiova',
            'category_slug' => 'editorial',
            'category_label' => 'Editorial',
            'slug' => 'curated-story-craiova',
            'summary' => 'Un articol selectat de redacție.',
            'is_highlighted' => true,
            'published_at' => Carbon::now()->subHour(),
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Știri craiova.ro', false)
            ->assertDontSee('Ultimele 10 știri', false)
            ->assertSee($post->title, false)
            ->assertSee('Selectat', false)
            ->assertSee(route('news-posts.show', ['category' => $post->category_slug, 'slug' => $post->slug]), false);
    }

    public function test_manual_post_detail_page_renders_content(): void
    {
        $post = NewsPost::factory()->create([
            'title' => 'Ghid complet pentru Craiova',
            'category_slug' => 'ghid-local',
            'category_label' => 'Ghid local',
            'slug' => 'ghid-complet-pentru-craiova',
            'body_html' => '<p>Conținutul articolului despre Craiova.</p>',
            'published_at' => Carbon::now()->subDay(),
        ]);

        $response = $this->get(route('news-posts.show', [
            'category' => $post->category_slug,
            'slug' => $post->slug,
        ]));

        $response->assertOk()
            ->assertSee($post->title, false)
            ->assertSee('craiova.ro', false)
            ->assertSee('Conținutul articolului despre Craiova.', false)
            ->assertSee('Ultimele 10 știri', false)
            ->assertSee('Știrile zilei', false)
            ->assertSee('Surse indexate', false);
    }
}
