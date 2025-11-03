<?php

namespace App\Console\Commands;

use App\Models\NewsSource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportNewsSourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:sources:export {--path= : Output file path relative to storage/app} {--pretty : Pretty-print JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all news sources as JSON for migration between environments.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sources = NewsSource::query()->orderBy('name')->get();

        if ($sources->isEmpty()) {
            $this->warn('No news sources to export.');

            return self::SUCCESS;
        }

        $payload = $sources->map(function (NewsSource $source): array {
            return $source->only([
                'name',
                'base_url',
                'source_type',
                'selector_type',
                'title_selector',
                'body_selector',
                'date_selector',
                'image_selector',
                'link_selector',
                'fetch_frequency',
                'keywords',
                'is_active',
                'scope',
            ]);
        })->values();

        $flags = $this->option('pretty') ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE : 0;
        $json = $payload->toJson($flags);

        $path = $this->option('path') ?: 'news_sources.json';
        Storage::put($path, $json);

        $this->info(sprintf('Exported %d sources to %s', $sources->count(), Storage::path($path)));

        return self::SUCCESS;
    }
}
