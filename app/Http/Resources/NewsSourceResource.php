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
        /** @var NewsSource $this */

        return [
            'id' => $this->id,
            'name' => $this->name,
            'base_url' => $this->base_url,
            'source_type' => $this->source_type,
            'selector_type' => $this->selector_type,
            'title_selector' => $this->title_selector,
            'body_selector' => $this->body_selector,
            'date_selector' => $this->date_selector,
            'image_selector' => $this->image_selector,
            'link_selector' => $this->link_selector,
            'fetch_frequency' => $this->fetch_frequency,
            'keywords' => $this->keywordsList(),
            'is_active' => (bool) $this->is_active,
            'last_fetched_at' => $this->last_fetched_at?->toIso8601String(),
            'last_fetch_status' => $this->last_fetch_status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'articles_count' => $this->when(isset($this->articles_count), $this->articles_count),
            'logs_count' => $this->when(isset($this->logs_count), $this->logs_count),
        ];
    }
}
