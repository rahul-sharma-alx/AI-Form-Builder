<?php

namespace App\Livewire\Imports;

use App\Models\Import;
use App\Services\ImportService;
use App\Support\FieldTypes;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Docx extends Component
{
    use WithFileUploads;

    public $file;

    public ?int $importId = null;

    public array $items = [];

    public string $title = '';

    public bool $mapped = false;

    public function upload()
    {
        $this->validate([
            'file' => 'required|file|mimes:docx|max:20480',
        ]);

        $import = app(ImportService::class)->begin($this->file);
        $this->importId = $import->id;
        $this->file = null;
    }

    public function getImportProperty(): ?Import
    {
        return $this->importId ? Import::find($this->importId) : null;
    }

    public function getFieldTypesProperty(): array
    {
        return array_values(array_diff(FieldTypes::all(), ['heading', 'section']));
    }

    public function createForm()
    {
        $this->validate([
            'title' => 'required|string|max:255',
        ]);

        $import = $this->import;

        if (! $import || $import->status !== 'completed') {
            return;
        }

        $form = app(ImportService::class)->buildForm($import, $this->title, $this->items);

        return redirect()->route('forms.builder', $form);
    }

    public function render()
    {
        if ($this->import && $this->import->status === 'completed' && ! $this->mapped) {
            $this->items = $this->mapItems($this->import->parsed_schema ?? []);
            $this->title = $this->title ?: $this->import->file_name;
            $this->mapped = true;
        }

        return view('livewire.imports.docx');
    }

    public function addQuestion()
    {
        $this->items[] = [
            'id' => (string) Str::uuid(),
            'type' => 'question',
            'label' => '',
            'field_type' => 'text',
            'options' => [],
        ];
    }

    public function addOption(int $index)
    {
        $this->items[$index]['options'][] = '';
    }

    public function removeOption(int $index, int $optionIndex)
    {
        unset($this->items[$index]['options'][$optionIndex]);
        $this->items[$index]['options'] = array_values($this->items[$index]['options']);
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    protected function mapItems(array $raw): array
    {
        return array_map(function (array $item) {
            if (($item['type'] ?? '') === 'heading') {
                return [
                    'id' => (string) Str::uuid(),
                    'type' => 'heading',
                    'label' => (string) ($item['text'] ?? ''),
                    'options' => [],
                ];
            }

            $options = array_values(array_map(fn ($option) => (string) $option, $item['options'] ?? []));

            return [
                'id' => (string) Str::uuid(),
                'type' => 'question',
                'label' => (string) ($item['text'] ?? ''),
                'field_type' => $options ? 'dropdown' : 'text',
                'options' => $options,
            ];
        }, $raw);
    }
}
