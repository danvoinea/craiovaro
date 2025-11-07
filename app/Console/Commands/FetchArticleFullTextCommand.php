<?php

namespace App\Console\Commands;

use App\Models\NewsRaw;
use App\Services\News\NewsScraperService;
use Illuminate\Console\Command;

class FetchArticleFullTextCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:articles:fetch-fulltext
        {--article=* : Specific news_raw IDs to refresh}
        {--source=* : Limit to particular news source IDs}
        {--limit=50 : Maximum number of articles to process when no IDs are supplied}
        {--force : Refresh even if full text already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch full article bodies for stored news_raw records.';

    public function __construct(private NewsScraperService $scraper)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $articleIds = $this->parseIdsOption('article');
        $sourceIds = $this->parseIdsOption('source');
        $limit = $this->parseLimitOption();
        $force = (bool) $this->option('force');

        $query = NewsRaw::query()
            ->with('source')
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        if ($articleIds !== []) {
            $query->whereIn('id', $articleIds);
        }

        if ($sourceIds !== []) {
            $query->whereIn('news_source_id', $sourceIds);
        }

        if ($articleIds === [] && ! $force) {
            $query->whereNull('body_text_full');
        }

        if ($articleIds === []) {
            $query->limit($limit);
        }

        $articles = $query->get();

        if ($articles->isEmpty()) {
            $this->warn('No news articles matched the selection.');

            return self::SUCCESS;
        }

        $totals = [
            'processed' => 0,
            'updated' => 0,
            'skipped' => 0,
            'filtered' => 0,
            'errors' => 0,
        ];

        foreach ($articles as $article) {
            $totals['processed']++;

            $result = $this->scraper->refreshArticleBody($article, $force);
            $status = $result['status'] ?? 'error';
            $message = $result['message'] ?? 'No additional details.';

            match ($status) {
                'updated' => $totals['updated']++,
                'skipped' => $totals['skipped']++,
                'filtered' => $totals['filtered']++,
                default => $totals['errors']++,
            };

            $this->line($this->formatStatus($status).sprintf(' Article #%d — %s', $article->id, $article->title));
            $this->line('  '.$message);
        }

        $this->newLine();
        $this->comment(sprintf(
            'Totals — processed: %d, updated: %d, skipped: %d, filtered: %d, errors: %d',
            $totals['processed'],
            $totals['updated'],
            $totals['skipped'],
            $totals['filtered'],
            $totals['errors']
        ));

        return self::SUCCESS;
    }

    /**
     * @return array<int, int>
     */
    protected function parseIdsOption(string $name): array
    {
        return collect((array) $this->option($name))
            ->filter(static fn ($value): bool => $value !== null && $value !== '')
            ->map(static fn ($value): int => (int) $value)
            ->unique()
            ->values()
            ->all();
    }

    protected function parseLimitOption(): int
    {
        $limit = (int) ($this->option('limit') ?? 50);

        return $limit > 0 ? $limit : 50;
    }

    protected function formatStatus(string $status): string
    {
        return match ($status) {
            'updated' => '<info>UPDATED</info>',
            'skipped' => '<comment>SKIP</comment>',
            'filtered' => '<comment>FILTERED</comment>',
            default => '<error>ERROR</error>',
        };
    }
}
