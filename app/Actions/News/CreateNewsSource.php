<?php

namespace App\Actions\News;

use App\Models\NewsSource;
use App\Services\News\NewsSourceService;

class CreateNewsSource
{
    public function __construct(
        protected NewsSourceService $service
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): NewsSource
    {
        return $this->service->create($attributes);
    }
}
