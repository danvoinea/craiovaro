<?php

namespace App\Services;

class SystemStatusService
{
    public function appInfo(): array
    {
        return [
            'name' => config('app.name'),
            'env' => config('app.env'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];
    }
}
