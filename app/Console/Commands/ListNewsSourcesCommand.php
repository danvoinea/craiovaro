<?php

namespace App\Console\Commands;

use App\Models\NewsSource;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ListNewsSourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:sources
        {--source=* : Filter by source IDs or names}
        {--all : Include inactive sources}
        {--detail : Display detailed configuration for each source}
        {--logs=3 : Number of recent logs to display when using --detail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List configured news sources and show recent scraper details.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sources = $this->resolveSources();

        if ($sources->isEmpty()) {
            $this->warn('No news sources matched the selection.');

            return self::SUCCESS;
        }

        $this->displaySummaryTable($sources);

        if ($this->option('detail')) {
            $this->displayDetails($sources);
        }

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, NewsSource>
     */
    protected function resolveSources(): Collection
    {
        $query = NewsSource::query()
            ->withCount('articles')
            ->with(['logs' => function ($logs): void {
                $logs->latest()->limit((int) $this->option('logs') ?: 3);
            }])
            ->orderBy('name');

        if (! $this->option('all')) {
            $query->where('is_active', true);
        }

        $filters = collect($this->option('source'))
            ->flatMap(function ($raw): array {
                return collect(explode(',', (string) $raw))
                    ->map(fn ($value): string => trim((string) $value))
                    ->filter()
                    ->all();
            })
            ->filter();

        if ($filters->isNotEmpty()) {
            $ids = $filters
                ->filter(fn ($value): bool => ctype_digit($value))
                ->map(fn ($value): int => (int) $value);
            $names = $filters->reject(fn ($value): bool => ctype_digit($value));

            $query->where(function (Builder $builder) use ($ids, $names): void {
                if ($ids->isNotEmpty()) {
                    $builder->orWhereIn('id', $ids->all());
                }

                if ($names->isNotEmpty()) {
                    $builder->orWhereIn('name', $names->all());
                }
            });
        }

        return $query->get();
    }

    protected function displaySummaryTable(Collection $sources): void
    {
        $rows = $sources->map(function (NewsSource $source): array {
            $lastFetched = $source->last_fetched_at?->setTimezone('Europe/Bucharest');
            $nextRun = $source->nextRunAt()?->setTimezone('Europe/Bucharest');
            $latestLog = $source->logs->first();

            return [
                'ID' => $source->id,
                'Name' => $source->name,
                'Status' => $source->is_active ? 'active' : 'inactive',
                'Type' => $source->source_type,
                'Frequency' => $source->fetch_frequency,
                'Last fetched' => $lastFetched ? $lastFetched->format('Y-m-d H:i') : '—',
                'Last status' => $source->last_fetch_status ?? 'unknown',
                'Articles' => number_format($source->articles_count ?? 0),
                'Next run' => $nextRun ? $nextRun->diffForHumans(Carbon::now('Europe/Bucharest'), parts: 2) : 'unknown',
                'Last log' => $latestLog?->created_at?->setTimezone('Europe/Bucharest')->format('Y-m-d H:i') ?? '—',
            ];
        })->all();

        $this->table([
            'ID',
            'Name',
            'Status',
            'Type',
            'Frequency',
            'Last fetched',
            'Last status',
            'Articles',
            'Next run',
            'Last log',
        ], $rows);
    }

    protected function displayDetails(Collection $sources): void
    {
        foreach ($sources as $source) {
            $this->newLine();
            $this->info(sprintf('%s [#%d]', $source->name, $source->id));

            $this->line(sprintf('  Base URL: %s', $source->base_url));
            $this->line(sprintf('  Selector type: %s', $source->selector_type ?? 'default (css)'));
            $this->line(sprintf('  Link selector: %s', $source->link_selector ?? '—'));
            $this->line(sprintf('  Title selector: %s', $source->title_selector ?? '—'));
            $this->line(sprintf('  Body selector: %s', $source->body_selector ?? '—'));
            $this->line(sprintf('  Date selector: %s', $source->date_selector ?? '—'));
            $this->line(sprintf('  Image selector: %s', $source->image_selector ?? '—'));

            $keywords = $source->keywordsList();
            $this->line(sprintf('  Keywords: %s', $keywords === [] ? '—' : implode(', ', $keywords)));

            $this->line('  Recent logs:');

            if ($source->logs->isEmpty()) {
                $this->line('    (no logs yet)');
            } else {
                foreach ($source->logs as $log) {
                    $ranAt = $log->ran_at?->setTimezone('Europe/Bucharest')->format('Y-m-d H:i');
                    $context = is_array($log->context) ? $log->context : (array) $log->context;
                    $summary = $context['processed'] ?? null;
                    $alloc = $summary !== null
                        ? sprintf(
                            'processed %d | new %d | updated %d | dup %d | filtered %d | errors %d',
                            $context['processed'] ?? 0,
                            $context['created'] ?? 0,
                            $context['updated'] ?? 0,
                            $context['duplicates'] ?? 0,
                            $context['filtered'] ?? 0,
                            $context['errors'] ?? 0,
                        )
                        : 'no summary';

                    $this->line(sprintf('    - [%s] %s — %s', strtoupper($log->status), $ranAt ?? 'unknown', $alloc));
                    if ($log->message) {
                        $this->line('      '.$log->message);
                    }
                }
            }
        }
    }
}
