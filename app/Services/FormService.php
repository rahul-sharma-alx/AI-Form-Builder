<?php

namespace App\Services;

use App\Models\Form;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FormService
{
    public function save(Form $form, array $payload): Form
    {
        return $this->persist($form, $payload, flash: true);
    }

    public function autosave(Form $form, array $payload): Form
    {
        return $this->persist($form, $payload, flash: false);
    }

    public function publish(Form $form): Form
    {
        $schemaChanged = $this->schemaChanged($form, $form->schema);

        $form->status = 'published';
        $form->published_at ??= Carbon::now();

        if ($schemaChanged) {
            $form->bumpVersion();
        }

        $form->last_saved_at = Carbon::now();
        $form->save();

        return $form;
    }

    public function unpublish(Form $form): Form
    {
        $form->status = 'draft';
        $form->published_at = null;
        $form->last_saved_at = Carbon::now();
        $form->save();

        return $form;
    }

    public function ensureSchemaShape(?array $schema, ?string $fallbackTitle = null): array
    {
        $schema ??= [];

        if (! isset($schema['title']) && $fallbackTitle !== null) {
            $schema['title'] = $fallbackTitle;
        }

        if (! isset($schema['steps']) || ! is_array($schema['steps']) || empty($schema['steps'])) {
            $schema['steps'] = [[
                'id' => (string) Str::uuid(),
                'title' => 'Step 1',
                'sections' => [[
                    'id' => (string) Str::uuid(),
                    'title' => 'Section 1',
                    'fields' => [],
                ]],
            ]];
        }

        return $schema;
    }

    protected function persist(Form $form, array $payload, bool $flash): Form
    {
        $schema = $this->ensureSchemaShape($payload['schema'] ?? [], $form->title);

        $schemaChanged = $this->schemaChanged($form, $schema);

        $form->title = (string) ($payload['title'] ?? $form->title);
        $form->description = $payload['description'] ?? $form->description;
        $form->schema = $schema;
        $form->settings = $payload['settings'] ?? $form->settings ?? [];
        $form->metadata = $payload['metadata'] ?? $form->metadata ?? [];

        if ($schemaChanged) {
            $form->bumpVersion();
        }

        $form->last_saved_at = Carbon::now();
        $form->save();

        if ($flash) {
            session()->flash('success', 'Form saved successfully.');
        }

        return $form;
    }

    protected function schemaChanged(Form $form, mixed $newSchema): bool
    {
        $current = $this->normalizeForCompare($form->schema);
        $next = $this->normalizeForCompare($newSchema);

        return $current !== $next;
    }

    protected function normalizeForCompare(mixed $schema): string
    {
        if (! is_array($schema)) {
            return '';
        }

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
