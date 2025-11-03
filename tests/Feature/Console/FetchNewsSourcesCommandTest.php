<?php

namespace Tests\Feature\Console;

use App\Models\NewsSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchNewsSourcesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_fetches_specified_sources(): void
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
            <title>Craiova command test</title>
            <link>https://example.com/articles/command</link>
            <description><![CDATA[<p>Command coverage for Craiova.</p>]]></description>
            <pubDate>Wed, 01 Jan 2025 12:00:00 +0000</pubDate>
        </item>
    </channel>
</rss>
XML;

        Http::fake([
            'https://example.com/feed' => Http::response($rss, 200),
            'https://example.com/articles/command' => Http::response('<html><body><article><h1>Craiova command test</h1><p>Body</p></article></body></html>', 200),
        ]);

        $this->artisan('news:fetch-sources', ['--source' => [$source->id], '--force' => true])
            ->expectsOutputToContain('RUN')
            ->expectsOutputToContain('Totals')
            ->assertExitCode(0);

        $this->assertDatabaseCount('news_raw', 1);
    }
}
