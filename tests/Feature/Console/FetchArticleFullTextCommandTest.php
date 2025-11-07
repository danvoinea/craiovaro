<?php

namespace Tests\Feature\Console;

use App\Models\NewsRaw;
use App\Models\NewsSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchArticleFullTextCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_fetches_full_text_for_missing_articles(): void
    {
        $source = NewsSource::factory()->create([
            'source_type' => 'rss',
            'title_selector' => 'h1',
            'body_selector' => 'article',
            'image_selector' => 'img.featured|src',
            'date_selector' => 'time|datetime',
            'keywords' => '',
            'scope' => 'local',
        ]);

        $articleUrl = 'https://example.com/articles/full-text';

        $article = NewsRaw::factory()
            ->for($source, 'source')
            ->create([
                'source_url' => $articleUrl,
                'url_hash' => hash('sha256', $articleUrl),
                'body_html' => null,
                'body_text' => null,
                'cover_image_url' => null,
                'meta' => ['summary' => '<p>Short recap</p>'],
            ]);

        $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <body>
        <article>
            <h1>Craiova coverage win</h1>
            <p>Paragraph one.</p>
            <p>Paragraph two.</p>
        </article>
        <time datetime="2025-11-05T09:00:00+02:00"></time>
        <img class="featured" src="https://example.com/images/poster.jpg" />
    </body>
</html>
HTML;

        Http::fake([
            $articleUrl => Http::response($html, 200),
        ]);

        $this->artisan('news:articles:fetch-fulltext', [
            '--article' => [$article->id],
        ])->assertExitCode(0);

        $this->assertDatabaseHas('news_raw', [
            'id' => $article->id,
            'body_text' => 'Craiova coverage win Paragraph one. Paragraph two.',
            'cover_image_url' => 'https://example.com/images/poster.jpg',
            'body_text_full' => 'Craiova coverage win Paragraph one. Paragraph two.',
        ]);
    }

    public function test_force_option_refreshes_existing_content(): void
    {
        $source = NewsSource::factory()->create([
            'source_type' => 'rss',
            'title_selector' => 'h1',
            'body_selector' => 'article',
            'image_selector' => 'img|src',
            'keywords' => '',
            'scope' => 'local',
        ]);

        $articleUrl = 'https://example.com/articles/force';

        $article = NewsRaw::factory()
            ->for($source, 'source')
            ->create([
                'source_url' => $articleUrl,
                'url_hash' => hash('sha256', $articleUrl),
                'body_html' => '<p>Old body</p>',
                'body_text' => 'Old body',
            ]);

        $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <body>
        <article>
            <h1>Updated Craiova piece</h1>
            <p>Fresh details.</p>
        </article>
    </body>
</html>
HTML;

        Http::fake([$articleUrl => Http::response($html, 200)]);

        $this->artisan('news:articles:fetch-fulltext', [
            '--article' => [$article->id],
        ])->assertExitCode(0);

        $this->assertDatabaseHas('news_raw', [
            'id' => $article->id,
            'body_text' => 'Old body',
            'title' => $article->title,
            'body_text_full' => 'Updated Craiova piece Fresh details.',
        ]);

        $this->artisan('news:articles:fetch-fulltext', [
            '--article' => [$article->id],
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('news_raw', [
            'id' => $article->id,
            'body_text' => 'Updated Craiova piece Fresh details.',
            'title' => 'Updated Craiova piece',
            'body_text_full' => 'Updated Craiova piece Fresh details.',
        ]);
    }
}
