<?php

namespace App\Actions\News;

use App\Models\NewsPost;
use App\Services\News\NewsPostService;

class UpdateNewsPost
{
    public function __construct(
        protected NewsPostService $service
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(NewsPost $post, array $attributes): NewsPost
    {
        return $this->service->update($post, $attributes);
    }
}
