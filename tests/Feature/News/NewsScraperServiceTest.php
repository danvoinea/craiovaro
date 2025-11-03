<?php

namespace Tests\Feature\News;

use App\Models\NewsRaw;
use App\Models\NewsSource;
use App\Models\NewsSourceLog;
use App\Services\News\NewsScraperService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsScraperServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_articles_from_rss_sources(): void
    {
        $source = NewsSource::factory()->create([
            'base_url' => 'https://example.com/feed',
            'source_type' => 'rss',
            'fetch_frequency' => 'hourly',
            'keywords' => 'craiova, dolj',
        ]);

        $rss = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>Example Feed</title>
        <item>
            <title>Craiova city update</title>
            <link>https://example.com/articles/new</link>
            <description><![CDATA[<p>Latest news from Craiova.</p>]]></description>
            <pubDate>Wed, 01 Jan 2025 12:00:00 +0000</pubDate>
        </item>
    </channel>
</rss>
XML;

        Http::fake([
            'https://example.com/feed' => Http::response($rss, 200),
            'https://example.com/articles/new' => Http::response('<html><body><article><h1>Craiova city update</h1><p>Full article body about Craiova.</p></article></body></html>', 200),
        ]);

        $service = $this->app->make(NewsScraperService::class);
        $result = $service->fetch($source);

        $this->assertSame('success', $result['status']);
        $this->assertSame(1, $result['summary']['created']);
        $this->assertDatabaseCount('news_raw', 1);
        $this->assertDatabaseHas('news_raw', [
            'title' => 'Craiova city update',
            'source_url' => 'https://example.com/articles/new',
        ]);
        $this->assertDatabaseCount('news_source_logs', 1);
    }

    public function test_it_skips_duplicate_articles_on_subsequent_runs(): void
    {
        $source = NewsSource::factory()->create([
            'base_url' => 'https://example.com/feed',
            'source_type' => 'rss',
            'fetch_frequency' => 'hourly',
            'keywords' => 'craiova',
        ]);

        $rss = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <item>
            <title>Craiova update</title>
            <link>https://example.com/articles/1</link>
            <description><![CDATA[<p>News about Craiova.</p>]]></description>
            <pubDate>Wed, 01 Jan 2025 12:00:00 +0000</pubDate>
        </item>
    </channel>
</rss>
XML;

        $service = $this->app->make(NewsScraperService::class);

        Http::fake([
            'https://example.com/feed' => Http::response($rss, 200),
            'https://example.com/articles/1' => Http::response('<html><body><article><h1>Craiova update</h1><p>Details.</p></article></body></html>', 200),
        ]);

        $service->fetch($source);

        Http::fake([
            'https://example.com/feed' => Http::response($rss, 200),
            'https://example.com/articles/1' => Http::response('<html><body><article><h1>Craiova update</h1><p>Details.</p></article></body></html>', 200),
        ]);

        $result = $service->fetch($source);

        $this->assertSame(1, NewsRaw::count());
        $this->assertSame(2, NewsSourceLog::count());
        $this->assertSame(1, $result['summary']['duplicates']);
    }
}
