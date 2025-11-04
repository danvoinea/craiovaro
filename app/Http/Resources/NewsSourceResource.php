<?php

namespace App\Http\Resources;

use App\Models\NewsSource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsSourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var NewsSource $resource */
        $resource = $this->resource;

        $data = [
            'id' => $resource->id,
            'name' => $resource->name,
            'base_url' => $resource->base_url,
            'source_type' => $resource->source_type,
            'selector_type' => $resource->selector_type,
            'title_selector' => $resource->title_selector,
            'body_selector' => $resource->body_selector,
            'date_selector' => $resource->date_selector,
            'image_selector' => $resource->image_selector,
            'link_selector' => $resource->link_selector,
            'fetch_frequency' => $resource->fetch_frequency,
            'keywords' => $resource->keywordsList(),
            'is_active' => (bool) $resource->is_active,
            'last_fetched_at' => optional($resource->last_fetched_at)->toIso8601String(),
            'last_fetch_status' => $resource->last_fetch_status,
            'created_at' => optional($resource->created_at)->toIso8601String(),
            'updated_at' => optional($resource->updated_at)->toIso8601String(),
        ];

        if (isset($resource->articles_count)) {
            $data['articles_count'] = $resource->articles_count;
        }

        if (isset($resource->logs_count)) {
            $data['logs_count'] = $resource->logs_count;
        }

        return $data;
    }
}
