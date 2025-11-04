<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateNewsPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $post = $this->route('newsPost');
        $categorySlug = $this->input('category_slug', $post?->category_slug);

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('news_posts')
                    ->where(fn ($query) => $query->where('category_slug', $categorySlug))
                    ->ignore($post),
            ],
            'category_slug' => ['sometimes', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'category_label' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'body_html' => ['nullable', 'string'],
            'hero_image_url' => ['nullable', 'url', 'max:2048'],
            'published_at' => ['sometimes', 'date'],
            'is_highlighted' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $categorySlug = $this->input('category_slug', $this->input('category'));

        if ($categorySlug !== null) {
            $categorySlug = Str::slug((string) $categorySlug);
        }

        $slug = $this->input('slug');

        if ($slug === null && $this->filled('title')) {
            $slug = Str::slug((string) $this->input('title'));
        } elseif ($slug !== null) {
            $slug = Str::slug((string) $slug);
        }

        $payload = [];

        if ($categorySlug !== null) {
            $payload['category_slug'] = $categorySlug;
        }

        if ($slug !== null) {
            $payload['slug'] = $slug ?: null;
        }

        if (! $this->filled('category_label') && $categorySlug) {
            $payload['category_label'] = Str::title(str_replace(['-', '_'], ' ', $categorySlug));
        }

        if ($this->has('is_highlighted')) {
            $payload['is_highlighted'] = $this->boolean('is_highlighted');
        }

        if ($this->has('is_published')) {
            $payload['is_published'] = $this->boolean('is_published');
        }

        $this->merge($payload);
    }
}
