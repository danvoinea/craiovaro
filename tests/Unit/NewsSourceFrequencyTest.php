<?php

namespace Tests\Unit;

use App\Models\NewsSource;
use App\Rules\ValidFetchFrequency;
use Illuminate\Support\Carbon;
use Illuminate\Translation\PotentiallyTranslatedString;
use Tests\TestCase;

use function app;

class NewsSourceFrequencyTest extends TestCase
{
    public function test_valid_fetch_frequency_allows_five_minute_preset(): void
    {
        $rule = new ValidFetchFrequency;

        $failed = false;

        $rule->validate('fetch_frequency', '5m', function (string $message) use (&$failed): PotentiallyTranslatedString {
            $failed = true;

            return new PotentiallyTranslatedString($message, app('translator'));
        });

        $this->assertFalse($failed, '5m preset should pass validation.');
    }

    public function test_is_due_to_run_supports_five_minute_frequency(): void
    {
        $source = new NewsSource([
            'fetch_frequency' => '5m',
            'is_active' => true,
        ]);

        $source->last_fetched_at = Carbon::parse('2025-01-01 12:00:00');

        $this->assertFalse(
            $source->isDueToRun(Carbon::parse('2025-01-01 12:04:59')),
            'Source should not be due before five minutes elapsed.'
        );

        $this->assertTrue(
            $source->isDueToRun(Carbon::parse('2025-01-01 12:05:00')),
            'Source should be due once five minutes have elapsed.'
        );
    }

    public function test_next_run_at_returns_five_minutes_later(): void
    {
        $source = new NewsSource([
            'fetch_frequency' => '5m',
            'is_active' => true,
        ]);

        $source->last_fetched_at = Carbon::parse('2025-01-01 12:00:00');

        $nextRun = $source->nextRunAt();

        $this->assertNotNull($nextRun);
        $this->assertTrue($nextRun->equalTo(Carbon::parse('2025-01-01 12:05:00')));
    }
}
