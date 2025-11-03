<?php

namespace App\Rules;

use Closure;
use Cron\CronExpression;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;

class ValidFetchFrequency implements ValidationRule
{
    /**
     * @var array<int, string>
     */
    protected array $presets = ['15m', 'hourly', 'daily'];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            $fail('The :attribute field must be a valid frequency or cron expression.');

            return;
        }

        $value = trim($value);

        if (in_array(mb_strtolower($value), $this->presets, true)) {
            return;
        }

        try {
            CronExpression::factory($value);
        } catch (InvalidArgumentException) {
            $fail('The :attribute field must be a valid cron expression or preset (15m, hourly, daily).');
        }
    }
}
