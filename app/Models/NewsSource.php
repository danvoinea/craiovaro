<?php

namespace App\Models;

use Cron\CronExpression;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class NewsSource extends Model
{
    /** @use HasFactory<\Database\Factories\NewsSourceFactory> */
    use HasFactory;

    protected $table = 'news_sources';

    protected $fillable = [
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
        'last_fetched_at',
        'last_fetch_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_fetched_at' => 'datetime',
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(NewsRaw::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(NewsSourceLog::class);
    }

    /**
     * @return array<int, string>
     */
    public function keywordsList(): array
    {
        if ($this->keywords === null) {
            return [];
        }

        return collect(explode(',', $this->keywords))
            ->map(static fn (string $keyword): string => trim(mb_strtolower($keyword)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function markFetched(Carbon $fetchedAt, string $status): void
    {
        $this->forceFill([
            'last_fetched_at' => $fetchedAt,
            'last_fetch_status' => $status,
        ])->save();
    }

    public function isDueToRun(Carbon $now): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $frequency = mb_strtolower($this->fetch_frequency);
        $lastRun = $this->last_fetched_at;

        return match ($frequency) {
            '15m' => $lastRun === null || $lastRun->diffInMinutes($now) >= 15,
            'hourly' => $lastRun === null || $lastRun->diffInMinutes($now) >= 60,
            'daily' => $lastRun === null || $lastRun->diffInHours($now) >= 24,
            default => $this->cronIsDue($frequency, $now, $lastRun),
        };
    }

    public function nextRunAt(): ?Carbon
    {
        $frequency = mb_strtolower($this->fetch_frequency);
        $reference = $this->last_fetched_at ?? Carbon::now();

        return match ($frequency) {
            '15m' => $reference->copy()->addMinutes(15),
            'hourly' => $reference->copy()->addHour(),
            'daily' => $reference->copy()->addDay(),
            default => $this->nextCronRun($frequency, $reference),
        };
    }

    protected function cronIsDue(string $expression, Carbon $now, ?Carbon $lastRun): bool
    {
        try {
            $cron = CronExpression::factory($expression);
        } catch (InvalidArgumentException) {
            return false;
        }

        $last = $lastRun ?? $now->copy()->subDay();

        $nextRun = Carbon::instance($cron->getNextRunDate($last, 0, true));

        return $nextRun->lessThanOrEqualTo($now);
    }

    protected function nextCronRun(string $expression, Carbon $reference): ?Carbon
    {
        try {
            $cron = CronExpression::factory($expression);
        } catch (InvalidArgumentException) {
            return null;
        }

        return Carbon::instance($cron->getNextRunDate($reference, 0, false));
    }
}
