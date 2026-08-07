<?php

namespace App\Support;

use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

class SchemaValidator
{
    public static function parseAndRepair(string $raw): array
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/\s*```$/', '', $raw);

        try {
            return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $fixed = preg_replace('/,\s*([}\]])/', '$1', $raw);

            return json_decode($fixed, true, 512, JSON_THROW_ON_ERROR);
        }
    }

    public static function validate(array $schema, ?string $fallbackTitle = null): array
    {
        $title = is_string($schema['title'] ?? null) && $schema['title'] !== ''
            ? $schema['title']
            : $fallbackTitle;

        $steps = is_array($schema['steps'] ?? null) ? array_values($schema['steps']) : [];

        if (! $steps) {
            throw new RuntimeException('AI schema must contain at least one step.');
        }

        $normalized = [
            'title' => $title,
            'steps' => array_map(fn ($step) => self::normalizeStep($step), $steps),
        ];

        return self::dedupeKeys($normalized);
    }

    protected static function normalizeStep(array $step): array
    {
        $sections = is_array($step['sections'] ?? null) ? array_values($step['sections']) : [];

        if (! $sections) {
            throw new RuntimeException('AI schema step must contain at least one section.');
        }

        return [
            'id' => (string) ($step['id'] ?? Str::uuid()->toString()),
            'title' => (string) ($step['title'] ?? 'Untitled Step'),
            'sections' => array_map(fn ($section) => self::normalizeSection($section), $sections),
        ];
    }

    protected static function normalizeSection(array $section): array
    {
        $fields = is_array($section['fields'] ?? null) ? array_values($section['fields']) : [];

        return [
            'id' => (string) ($section['id'] ?? Str::uuid()->toString()),
            'title' => (string) ($section['title'] ?? 'Untitled Section'),
            'fields' => array_map(fn ($field) => self::normalizeField($field), $fields),
        ];
    }

    protected static function normalizeField(array $field): array
    {
        $type = (string) ($field['type'] ?? 'text');
        $type = in_array($type, FieldTypes::all(), true) ? $type : 'text';

        $label = (string) ($field['label'] ?? ucfirst($type));
        $id = (string) ($field['id'] ?? Str::uuid()->toString());

        $key = is_string($field['key'] ?? null) ? Str::slug($field['key'], '_') : '';
        if ($key === '' || $key === 'field') {
            $key = FieldFactory::generateKey($label, $id);
        }

        $normalized = array_merge(FieldFactory::make($type), [
            'id' => $id,
            'key' => $key,
            'type' => $type,
            'label' => $label,
            'required' => ! empty($field['required']),
        ]);

        foreach (['placeholder', 'help', 'default', 'min', 'max', 'regex', 'validation'] as $prop) {
            if (array_key_exists($prop, $field) && $field[$prop] !== null && $field[$prop] !== '') {
                $normalized[$prop] = $field[$prop];
            }
        }

        if (isset($field['options']) && is_array($field['options'])) {
            $normalized['options'] = array_values(array_map(
                fn ($option) => is_array($option)
                    ? [
                        'label' => (string) ($option['label'] ?? $option['value'] ?? ''),
                        'value' => (string) ($option['value'] ?? ''),
                    ]
                    : ['label' => (string) $option, 'value' => (string) $option],
                $field['options']
            ));
        }

        return $normalized;
    }

    protected static function dedupeKeys(array $schema): array
    {
        $seen = [];

        foreach ($schema['steps'] as &$step) {
            foreach ($step['sections'] as &$section) {
                foreach ($section['fields'] as &$field) {
                    if (isset($seen[$field['key']])) {
                        $seen[$field['key']]++;

                        $field['key'] = $field['key'].'_'.$seen[$field['key']];
                    } else {
                        $seen[$field['key']] = 1;
                    }
                }
                unset($field);
            }
            unset($section);
        }
        unset($step);

        return $schema;
    }
}
