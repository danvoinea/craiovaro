<?php

namespace App\Http\Requests;

use App\Models\NewsSource;
use App\Rules\ValidFetchFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateNewsSourceRequest extends FormRequest
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
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'base_url' => ['sometimes', 'url', 'max:2048'],
            'source_type' => ['sometimes', 'string', 'in:rss,html,sitemap'],
            'selector_type' => ['sometimes', 'string', 'in:css,xpath'],
            'title_selector' => ['sometimes', 'nullable', 'string', 'max:255'],
            'body_selector' => ['sometimes', 'nullable', 'string', 'max:255'],
            'date_selector' => ['sometimes', 'nullable', 'string', 'max:255'],
            'image_selector' => ['sometimes', 'nullable', 'string', 'max:255'],
            'link_selector' => ['sometimes', 'nullable', 'string', 'max:255'],
            'fetch_frequency' => ['sometimes', 'string', new ValidFetchFrequency],
            'keywords' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var NewsSource|null $source */
            $source = $this->route('newsSource');
            $sourceType = $this->input('source_type', $source?->source_type);
            $linkSelector = $this->has('link_selector')
                ? $this->input('link_selector')
                : $source?->link_selector;

            if ($sourceType === 'html' && blank($linkSelector)) {
                $validator->errors()->add('link_selector', 'The link selector field is required for HTML sources.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if (is_array($this->input('keywords'))) {
            $keywords = collect($this->input('keywords'))
                ->map(static fn ($keyword): string => is_string($keyword) ? trim($keyword) : '')
                ->filter()
                ->implode(', ');

            $this->merge(['keywords' => $keywords]);
        }
    }
}
