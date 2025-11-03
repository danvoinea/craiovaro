<?php

namespace App\Http\Requests;

use App\Rules\ValidFetchFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreNewsSourceRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'url', 'max:2048'],
            'source_type' => ['required', 'string', 'in:rss,html,sitemap'],
            'selector_type' => ['nullable', 'string', 'in:css,xpath'],
            'title_selector' => ['nullable', 'string', 'max:255'],
            'body_selector' => ['nullable', 'string', 'max:255'],
            'date_selector' => ['nullable', 'string', 'max:255'],
            'image_selector' => ['nullable', 'string', 'max:255'],
            'link_selector' => ['nullable', 'string', 'max:255'],
            'fetch_frequency' => ['required', 'string', new ValidFetchFrequency],
            'keywords' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('source_type') === 'html' && blank($this->input('link_selector'))) {
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

        if (! $this->has('selector_type')) {
            $this->merge(['selector_type' => 'css']);
        }
    }
}
