<?php

namespace App\Actions\News;

use App\Models\NewsSource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListNewsSources
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(array $filters = []): LengthAwarePaginator
    {
        $query = NewsSource::query()
            ->withCount(['articles', 'logs'])
            ->orderByDesc('is_active')
            ->orderBy('name');

        if (array_key_exists('is_active', $filters)) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (array_key_exists('source_type', $filters) && filled($filters['source_type'])) {
            $query->where('source_type', $filters['source_type']);
        }

        return $query->paginate(
            perPage: (int) ($filters['per_page'] ?? 15),
            page: (int) ($filters['page'] ?? null)
        );
    }
}
