<?php

namespace App\Livewire\Imports;

use App\Jobs\ProcessExcelImportJob;
use App\Models\Import;
use App\Services\ImportService;
use App\Support\ExcelReader;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class Excel extends Component
{
    use WithFileUploads;

    public $file;

    public ?int $importId = null;

    public array $previewRows = [];

    public array $columns = [];

    public array $mapping = [];

    public bool $hasHeader = true;

    public string $title = '';

    public string $status = '';

    public function upload()
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $import = app(ImportService::class)->begin($this->file, 'xlsx');
        $this->importId = $import->id;
        $this->file = null;

        $rows = ExcelReader::preview(Storage::path($import->file_path), 15);
        $this->previewRows = array_map(fn ($row) => $this->normalizeRow($row), $rows);
        $this->columns = $this->detectColumns($this->previewRows);
        $this->mapping = app(ImportService::class)->detectMapping($this->previewRows, $this->hasHeader);
        $this->title = $import->file_name;
        $this->status = 'preview';
    }

    public function createForm()
    {
        $this->validate([
            'title' => 'required|string|max:255',
        ]);

        if (! in_array('label', $this->mapping, true)) {
            $this->addError('mapping', 'Map at least one column to "Label".');
            return;
        }

        $import = $this->import;
        if (! $import) {
            return;
        }

        ProcessExcelImportJob::dispatch($import, $this->mapping, $this->title, $this->hasHeader);

        $this->status = 'processing';
    }

    public function getImportProperty(): ?Import
    {
        return $this->importId ? Import::find($this->importId) : null;
    }

    public function getPropertiesProperty(): array
    {
        return ['label', 'type', 'required', 'placeholder', 'help', 'options', 'section', 'validation', 'ignore'];
    }

    public function render()
    {
        return view('livewire.imports.excel');
    }

    protected function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach (array_values($row) as $index => $value) {
            $normalized[Coordinate::stringFromColumnIndex($index + 1)] = $value;
        }

        return $normalized;
    }

    protected function detectColumns(array $rows): array
    {
        $count = 0;
        foreach ($rows as $row) {
            $count = max($count, count($row));
        }

        $columns = [];
        for ($i = 1; $i <= $count; $i++) {
            $columns[] = Coordinate::stringFromColumnIndex($i);
        }

        return $columns;
    }
}
