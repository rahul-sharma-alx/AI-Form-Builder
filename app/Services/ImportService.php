<?php

namespace App\Services;

use App\Jobs\ProcessDocxImportJob;
use App\Models\Form;
use App\Models\Import;
use App\Support\DocxParser;
use App\Support\FieldFactory;
use App\Support\FieldTypes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ImportService
{
    public function begin(UploadedFile $file, string $type = 'docs'): Import
    {
        $path = $file->store('imports', ['disk' => config('filesystems.default')]);

        $import = Import::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'type' => $type,
            'status' => 'pending',
        ]);

        if ($type === 'docs') {
            ProcessDocxImportJob::dispatch($import);
        }

        return $import;
    }

    public function processDocs(Import $import): void
    {
        $import->update(['status' => 'processing']);

        $items = DocxParser::parse(Storage::path($import->file_path));

        $import->update([
            'status' => 'completed',
            'parsed_schema' => $items,
        ]);
    }

    public function processExcel(Import $import, array $mapping, string $title, bool $hasHeader): void
    {
        $rows = \App\Support\ExcelReader::rows(Storage::path($import->file_path));

        $items = $this->buildItemsFromRows($rows, $mapping, $hasHeader);

        $form = $this->buildForm($import, $title, $items);

        $import->update([
            'status' => 'completed',
            'parsed_schema' => $form->schema,
        ]);
    }

    public function detectMapping(array $rows, bool $hasHeader): array
    {
        $synonyms = [
            'label' => ['label', 'question', 'question label', 'field label', 'title'],
            'type' => ['type', 'field type', 'fieldtype'],
            'required' => ['required'],
            'placeholder' => ['placeholder'],
            'help' => ['help', 'help text'],
            'options' => ['options', 'choices'],
            'section' => ['section'],
            'validation' => ['validation', 'rules'],
        ];

        $header = $hasHeader ? ($rows[0] ?? []) : [];

        $mapping = [];
        foreach ($rows as $row) {
            foreach (array_keys($row) as $column) {
                $mapping[$column] ??= 'ignore';
            }
        }

        if (! $header) {
            return $mapping;
        }

        foreach ($mapping as $column => $prop) {
            $name = strtolower(trim((string) ($header[$column] ?? '')));

            foreach ($synonyms as $target => $names) {
                if (in_array($name, $names, true)) {
                    $mapping[$column] = $target;
                    break;
                }
            }
        }

        return $mapping;
    }

    public function buildItemsFromRows(array $rows, array $mapping, bool $hasHeader): array
    {
        $indexByProp = [];
        foreach ($mapping as $column => $prop) {
            if ($prop === 'ignore') {
                continue;
            }
            $indexByProp[$prop] = Coordinate::columnIndexFromString($column) - 1;
        }

        $items = [];
        $currentSection = null;

        foreach ($rows as $index => $row) {
            if ($hasHeader && $index === 0) {
                continue;
            }

            $cells = array_values($row);

            $label = $this->cell($cells, $indexByProp['label'] ?? null);
            if ($label === null || trim((string) $label) === '') {
                continue;
            }

            $type = strtolower((string) $this->cell($cells, $indexByProp['type'] ?? null));
            if ($type === '' || ! in_array($type, FieldTypes::all(), true) || in_array($type, ['heading', 'section'], true)) {
                $type = 'text';
            }

            $section = trim((string) $this->cell($cells, $indexByProp['section'] ?? null));
            if ($section !== '' && $section !== $currentSection) {
                $items[] = ['type' => 'heading', 'label' => $section];
                $currentSection = $section;
            }

            $item = [
                'type' => 'question',
                'label' => trim((string) $label),
                'field_type' => $type,
                'options' => $this->parseOptions($this->cell($cells, $indexByProp['options'] ?? null)),
            ];

            $required = $this->cell($cells, $indexByProp['required'] ?? null);
            if ($required !== null && $required !== '') {
                $item['required'] = $this->boolValue($required);
            }

            foreach (['placeholder', 'help', 'validation'] as $prop) {
                $value = $this->cell($cells, $indexByProp[$prop] ?? null);
                if ($value !== null && $value !== '') {
                    $item[$prop] = (string) $value;
                }
            }

            $items[] = $item;
        }

        return $items;
    }

    protected function cell(array $cells, ?int $index): mixed
    {
        return $index === null ? null : ($cells[$index] ?? null);
    }

    protected function parseOptions(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $string = (string) $value;

        if (str_starts_with($string, '[')) {
            $decoded = json_decode($string, true);
            if (is_array($decoded)) {
                return array_values(array_map(fn ($option) => (string) $option, $decoded));
            }
        }

        return array_values(array_filter(array_map('trim', preg_split('/[|\n]/', $string)), fn ($option) => $option !== ''));
    }

    protected function boolValue(mixed $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'y'], true);
    }

    public function buildForm(Import $import, string $title, array $items): Form
    {
        $form = Form::create([
            'title' => $title,
            'schema' => $this->buildSchema($items, $title),
            'status' => 'draft',
        ]);

        $import->update(['form_id' => $form->id]);

        return $form;
    }

    public function buildSchema(array $items, string $title): array
    {
        $sections = [];

        foreach ($items as $item) {
            if (($item['type'] ?? '') === 'heading') {
                $sections[] = [
                    'id' => (string) Str::uuid(),
                    'title' => trim((string) ($item['label'] ?? '')) ?: 'Section '.(count($sections) + 1),
                    'fields' => [],
                ];

                continue;
            }

            if (($item['type'] ?? '') !== 'question') {
                continue;
            }

            if (empty($sections)) {
                $sections[] = ['id' => (string) Str::uuid(), 'title' => 'Section 1', 'fields' => []];
            }

            $field = FieldFactory::make($item['field_type'] ?? 'text');
            $field['label'] = trim((string) ($item['label'] ?? '')) ?: $field['label'];
            $field['key'] = FieldFactory::generateKey($field['label'], $field['id']);

            foreach (['required', 'placeholder', 'help', 'validation'] as $prop) {
                if (array_key_exists($prop, $item) && $item[$prop] !== null && $item[$prop] !== '') {
                    $field[$prop] = $prop === 'required' ? (bool) $item[$prop] : $item[$prop];
                }
            }

            $options = $item['options'] ?? [];
            if (is_array($options) && $options) {
                $field['options'] = array_map(
                    fn ($option) => [
                        'label' => (string) $option,
                        'value' => Str::slug((string) $option, '_') ?: Str::uuid()->toString(),
                    ],
                    $options
                );
            }

            $sections[array_key_last($sections)]['fields'][] = $field;
        }

        if (empty($sections)) {
            $sections[] = ['id' => (string) Str::uuid(), 'title' => 'Section 1', 'fields' => []];
        }

        return [
            'title' => $title,
            'steps' => [[
                'id' => (string) Str::uuid(),
                'title' => 'Step 1',
                'sections' => array_values($sections),
            ]],
        ];
    }
}
