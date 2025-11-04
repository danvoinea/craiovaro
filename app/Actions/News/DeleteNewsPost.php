<?php

namespace App\Actions\News;

use App\Models\NewsPost;
use App\Services\News\NewsPostService;

class DeleteNewsPost
{
    public function __construct(
        protected NewsPostService $service
    ) {}

    public function execute(NewsPost $post): void
    {
        $this->service->delete($post);
    }
}
