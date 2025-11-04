<?php

namespace App\Http\Resources;

use App\Models\NewsPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var NewsPost $resource */
        $resource = $this->resource;

        $data = [
            'id' => $resource->id,
            'title' => $resource->title,
            'slug' => $resource->slug,
            'category_slug' => $resource->category_slug,
            'category_label' => $resource->category_label,
            'summary' => $resource->summary,
            'hero_image_url' => $resource->hero_image_url,
            'published_at' => optional($resource->published_at)->toIso8601String(),
            'is_highlighted' => (bool) $resource->is_highlighted,
            'is_published' => (bool) $resource->is_published,
            'created_at' => optional($resource->created_at)->toIso8601String(),
            'updated_at' => optional($resource->updated_at)->toIso8601String(),
        ];

        if ($request->boolean('include_body')) {
            $data['body_html'] = $resource->body_html;
        }

        return $data;
    }
}
