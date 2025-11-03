<?php

namespace App\Actions\News;

use App\Models\NewsSource;
use App\Services\News\NewsSourceService;

class UpdateNewsSource
{
    public function __construct(
        protected NewsSourceService $service
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(NewsSource $source, array $attributes): NewsSource
    {
        return $this->service->update($source, $attributes);
    }
}
