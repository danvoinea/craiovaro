<?php

namespace App\Actions\News;

use App\Models\NewsPost;
use App\Services\News\NewsPostService;

class CreateNewsPost
{
    public function __construct(
        protected NewsPostService $service
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): NewsPost
    {
        return $this->service->create($attributes);
    }
}
