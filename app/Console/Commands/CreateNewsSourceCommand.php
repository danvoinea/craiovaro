<?php

namespace App\Console\Commands;

use App\Models\NewsSource;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateNewsSourceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:sources:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactively create a new news source.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $data = $this->collectSourceData();

        /** @var NewsSource $source */
        $source = NewsSource::query()->create($data);

        $this->info(sprintf('Created news source #%d: %s', $source->id, $source->name));

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    protected function collectSourceData(): array
    {
        $name = $this->askRequired('Source name');
        $baseUrl = $this->askRequired('Base URL');

        $sourceType = $this->choice(
            'Source type',
            ['rss', 'sitemap', 'html'],
            default: 'rss'
        );

        if ($sourceType === 'rss') {
            $selectorType = $this->confirm('Override default selector type? (default: css)', false)
                ? $this->choice('Selector type', ['css', 'xpath'], default: 'css')
                : null;
        } else {
            $selectorType = $this->choice('Selector type', ['css', 'xpath'], default: 'css');
        }

        $linkSelector = $sourceType === 'rss'
            ? null
            : $this->askRequired('Link selector (CSS/XPath)');

        $titleSelector = $this->askOptional('Title selector (optional)');
        $bodySelector = $this->askOptional('Body selector (optional)');
        $dateSelector = $this->askOptional('Date selector (optional)');
        $imageSelector = $this->askOptional('Image selector (optional)');

        $frequency = $this->askRequired('Fetch frequency (cron, daily, hourly, 15m, etc.)');
        $scope = $this->choice('Scope', ['local', 'national'], default: 'local');
        $keywordsRaw = $this->askOptional('Keywords (comma separated, optional)');

        $isActive = $this->confirm('Activate this source?', true);

        return Arr::whereNotNull([
            'name' => $name,
            'base_url' => $baseUrl,
            'source_type' => $sourceType,
            'selector_type' => $selectorType ?: null,
            'link_selector' => $linkSelector,
            'title_selector' => $titleSelector,
            'body_selector' => $bodySelector,
            'date_selector' => $dateSelector,
            'image_selector' => $imageSelector,
            'fetch_frequency' => $frequency,
            'keywords' => $keywordsRaw ? $this->normaliseKeywords($keywordsRaw) : null,
            'is_active' => $isActive,
            'scope' => $scope,
        ]);
    }

    protected function askRequired(string $question): string
    {
        do {
            $value = (string) $this->ask($question);
        } while (trim($value) === '');

        return trim($value);
    }

    protected function askOptional(string $question): ?string
    {
        $value = $this->ask($question);

        return $value !== null && trim($value) !== '' ? trim($value) : null;
    }

    protected function normaliseKeywords(string $keywords): string
    {
        return Str::of($keywords)
            ->explode(',')
            ->map(fn ($value): string => trim(mb_strtolower($value)))
            ->filter()
            ->implode(', ');
    }
}
