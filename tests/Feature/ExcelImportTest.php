<?php

namespace Tests\Feature;

use App\Jobs\ProcessExcelImportJob;
use App\Livewire\Imports\Excel;
use App\Models\Import;
use App\Services\ImportService;
use App\Support\ExcelReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportTest extends TestCase
{
    use RefreshDatabase;

    protected function writeXlsx(array $rows): string
    {
        $path = sys_get_temp_dir().'/edunett_xls_'.Str::uuid().'.xlsx';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows);
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    protected function sampleRows(): array
    {
        return [
            ['type', 'label', 'required', 'options'],
            ['text', 'Full name', 'yes', ''],
            ['dropdown', 'Color', 'no', 'Red|Blue'],
        ];
    }

    public function test_reader_preview_is_bounded_and_full_read_returns_all_rows(): void
    {
        $rows = [['type', 'label']];
        foreach (range(1, 20) as $i) {
            $rows[] = ['text', "Q{$i}"];
        }

        $path = $this->writeXlsx($rows);

        $this->assertCount(15, ExcelReader::preview($path, 15));
        $this->assertCount(21, ExcelReader::rows($path));
    }

    public function test_detect_mapping_auto_maps_standard_headers(): void
    {
        $preview = [
            ['A' => 'type', 'B' => 'label', 'C' => 'required', 'D' => 'options'],
            ['A' => 'text', 'B' => 'Full name', 'C' => 'yes', 'D' => ''],
        ];

        $mapping = app(ImportService::class)->detectMapping($preview, true);

        $this->assertSame('type', $mapping['A']);
        $this->assertSame('label', $mapping['B']);
        $this->assertSame('required', $mapping['C']);
        $this->assertSame('options', $mapping['D']);
    }

    public function test_build_items_from_rows_parses_mapping(): void
    {
        $rows = [
            ['type', 'label', 'required', 'options', 'section'],
            ['text', 'Full name', 'yes', '', 'Personal'],
            ['dropdown', 'Color', 'no', 'Red|Blue', 'Personal'],
        ];

        $mapping = ['A' => 'type', 'B' => 'label', 'C' => 'required', 'D' => 'options', 'E' => 'section'];

        $items = app(ImportService::class)->buildItemsFromRows($rows, $mapping, true);

        $this->assertSame('heading', $items[0]['type']);
        $this->assertSame('Personal', $items[0]['label']);

        $this->assertSame('question', $items[1]['type']);
        $this->assertSame('Full name', $items[1]['label']);
        $this->assertSame('text', $items[1]['field_type']);
        $this->assertTrue($items[1]['required']);

        $this->assertSame('Color', $items[2]['label']);
        $this->assertSame('dropdown', $items[2]['field_type']);
        $this->assertSame(['Red', 'Blue'], $items[2]['options']);
    }

    public function test_job_creates_form_from_file(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'questions.xlsx',
            file_get_contents($this->writeXlsx($this->sampleRows())),
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $import = app(ImportService::class)->begin($file, 'xlsx');

        $mapping = ['A' => 'type', 'B' => 'label', 'C' => 'required', 'D' => 'options'];

        (new ProcessExcelImportJob($import, $mapping, 'Excel Form', true))->handle(app(ImportService::class));

        $import->refresh();

        $this->assertSame('completed', $import->status);
        $this->assertNotNull($import->form);

        $fields = $import->form->schema['steps'][0]['sections'][0]['fields'];
        $this->assertCount(2, $fields);
        $this->assertSame('Full name', $fields[0]['label']);
        $this->assertTrue($fields[0]['required']);
        $this->assertSame(['label' => 'Red', 'value' => 'red'], $fields[1]['options'][0]);
    }

    public function test_upload_previews_maps_and_creates_form(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'questions.xlsx',
            file_get_contents($this->writeXlsx($this->sampleRows())),
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $component = Livewire::test(Excel::class)
            ->set('file', $file)
            ->call('upload');

        $component
            ->assertSet('status', 'preview')
            ->assertSet('title', 'questions.xlsx');

        $import = Import::findOrFail($component->get('importId'));
        $this->assertSame('pending', $import->status);
        $this->assertNotEmpty($component->get('previewRows'));
        $this->assertSame('type', $component->get('mapping')['A']);
        $this->assertSame('label', $component->get('mapping')['B']);

        $component->set('title', 'Excel Import')->call('createForm');

        $import->refresh();
        $this->assertSame('completed', $import->status);
        $this->assertNotNull($import->form);
        $this->assertSame('Excel Import', $import->form->title);
    }

    public function test_template_route_downloads_xlsx(): void
    {
        $response = $this->get(route('imports.xlsx.template'));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheet', $response->headers->get('content-type'));
    }
}
