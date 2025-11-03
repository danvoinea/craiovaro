<?php

namespace App\Console\Commands;

use App\Models\NewsSource;
use App\Services\News\NewsScraperService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class FetchNewsSourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch-sources {--source=* : IDs of the sources to fetch} {--force : Ignore scheduling rules}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news articles from configured sources.';

    public function __construct(
        protected NewsScraperService $scraper
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourceIds = collect($this->option('source'))
            ->filter(fn ($id): bool => $id !== null && $id !== '')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $query = NewsSource::query();

        if ($sourceIds !== []) {
            $query->whereIn('id', $sourceIds);
        } else {
            $query->where('is_active', true);
        }

        $sources = $query->orderBy('name')->get();

        if ($sources->isEmpty()) {
            $this->warn('No news sources matched the selection.');

            return self::SUCCESS;
        }

        $now = Carbon::now();
        $force = (bool) $this->option('force');

        $totals = [
            'processed' => 0,
            'created' => 0,
            'duplicates' => 0,
            'filtered' => 0,
            'errors' => 0,
            'skipped' => 0,
        ];

        foreach ($sources as $source) {
            if (! $force && ! $source->isDueToRun($now)) {
                $totals['skipped']++;

                $next = $source->nextRunAt();
                $this->line(sprintf(
                    '<comment>SKIP</comment> %s (next run %s)',
                    $source->name,
                    $next?->diffForHumans($now, [
                        'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
                        'parts' => 2,
                    ]) ?? 'unknown'
                ));

                continue;
            }

            $this->line(sprintf('<info>RUN</info> %s [%s]', $source->name, $source->fetch_frequency));

            $result = $this->scraper->fetch($source);

            $totals['processed'] += $result['summary']['processed'] ?? 0;
            $totals['created'] += $result['summary']['created'] ?? 0;
            $totals['duplicates'] += $result['summary']['duplicates'] ?? 0;
            $totals['filtered'] += $result['summary']['filtered'] ?? 0;
            $totals['errors'] += $result['summary']['errors'] ?? 0;

            if (($result['status'] ?? 'success') === 'success') {
                $this->info('  ✓ '.$result['message']);
            } else {
                $this->error('  ✕ '.$result['message']);
            }
        }

        $this->newLine();
        $this->comment(sprintf(
            'Totals — processed: %d, new: %d, duplicates: %d, filtered: %d, errors: %d, skipped: %d',
            $totals['processed'],
            $totals['created'],
            $totals['duplicates'],
            $totals['filtered'],
            $totals['errors'],
            $totals['skipped']
        ));

        return self::SUCCESS;
    }
}
