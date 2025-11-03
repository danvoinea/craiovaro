<?php

namespace App\Actions\News;

use App\Models\NewsSource;
use App\Services\News\NewsSourceService;

class DeleteNewsSource
{
    public function __construct(
        protected NewsSourceService $service
    ) {}

    public function execute(NewsSource $source): void
    {
        $this->service->delete($source);
    }
}
