<?php

namespace App\Actions\News;

use App\Models\NewsPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListNewsPosts
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(1, min($perPage, 100));

        $query = NewsPost::query()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        if (array_key_exists('is_published', $filters)) {
            $value = filter_var($filters['is_published'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($value !== null) {
                $query->where('is_published', $value);
            }
        }

        if (array_key_exists('is_highlighted', $filters)) {
            $value = filter_var($filters['is_highlighted'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($value !== null) {
                $query->where('is_highlighted', $value);
            }
        }

        if (! empty($filters['category'])) {
            $query->where('category_slug', $filters['category']);
        }

        return $query->paginate($perPage);
    }
}
