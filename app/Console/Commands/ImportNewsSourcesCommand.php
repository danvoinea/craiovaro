<?php

namespace App\Console\Commands;

use App\Models\NewsSource;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportNewsSourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:sources:import {--path=news_sources.json : Input file path relative to storage/app} {--force : Overwrite existing sources with the same name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import news sources from a JSON export file.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->option('path');

        if (! Storage::exists($path)) {
            $this->error(sprintf('File not found: %s', Storage::path($path)));

            return self::FAILURE;
        }

        $json = Storage::get($path);

        $data = json_decode($json, true);

        if (! is_array($data)) {
            $this->error('Invalid JSON payload.');

            return self::FAILURE;
        }

        $imported = 0;
        $updated = 0;

        foreach ($data as $payload) {
            if (! is_array($payload)) {
                continue;
            }

            $attributes = $this->sanitize($payload);

            if (! isset($attributes['name'], $attributes['base_url'], $attributes['source_type'])) {
                $this->warn('Skipping entry missing required fields.');

                continue;
            }

            $existing = NewsSource::query()->where('name', $attributes['name'])->first();

            if ($existing && ! $this->option('force')) {
                $this->line(sprintf('Skipping existing source: %s', $existing->name));

                continue;
            }

            if ($existing) {
                $existing->fill($attributes)->save();
                $updated++;
            } else {
                NewsSource::query()->create($attributes);
                $imported++;
            }
        }

        $this->info(sprintf('Imported %d sources, updated %d sources.', $imported, $updated));

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function sanitize(array $payload): array
    {
        $attributes = Arr::only($payload, [
            'name',
            'base_url',
            'source_type',
            'selector_type',
            'title_selector',
            'body_selector',
            'date_selector',
            'image_selector',
            'link_selector',
            'fetch_frequency',
            'keywords',
            'is_active',
        ]);

        if (isset($attributes['selector_type']) && $attributes['selector_type'] === '') {
            $attributes['selector_type'] = null;
        }

        if (isset($attributes['keywords'])) {
            $attributes['keywords'] = Str::of((string) $attributes['keywords'])
                ->explode(',')
                ->map(fn ($value): string => trim($value))
                ->filter()
                ->implode(', ');
        }

        if (isset($attributes['is_active'])) {
            $attributes['is_active'] = (bool) $attributes['is_active'];
        }

        return $attributes;
    }
}
