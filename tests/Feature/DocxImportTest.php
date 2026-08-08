<?php

namespace Tests\Feature;

use App\Livewire\Imports\Docx;
use App\Models\Import;
use App\Services\ImportService;
use App\Support\DocxParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Tests\TestCase;

class DocxImportTest extends TestCase
{
    use RefreshDatabase;

    protected function writeDocx(): string
    {
        $path = sys_get_temp_dir().'/edunett_test_'.Str::uuid().'.docx';

        $phpWord = new PhpWord;
        $phpWord->addParagraphStyle('Heading1', ['basedOn' => 'Normal']);
        $section = $phpWord->addSection();
        $section->addText('Section 1: Personal Details', [], 'Heading1');
        $section->addText('Full name');
        $section->addText('Favorite color');
        $section->addText("\u{2610} Red");
        $section->addText("\u{2610} Blue");
        $section->addText('Email');

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }

    public function test_parser_extracts_headings_questions_and_options(): void
    {
        $items = DocxParser::parse($this->writeDocx());

        $this->assertSame('heading', $items[0]['type']);
        $this->assertSame('Section 1: Personal Details', $items[0]['text']);
        $this->assertSame('Full name', $items[1]['text']);
        $this->assertSame([], $items[1]['options']);
        $this->assertSame('Favorite color', $items[2]['text']);
        $this->assertSame(['Red', 'Blue'], $items[2]['options']);
        $this->assertSame('Email', $items[3]['text']);
    }

    public function test_process_docs_stores_parsed_items_on_import(): void
    {
        Storage::fake('local');
        $path = 'imports/'.Str::uuid().'.docx';
        Storage::put($path, file_get_contents($this->writeDocx()));

        $import = Import::create([
            'file_name' => 'exam.docx',
            'file_path' => $path,
            'type' => 'docs',
            'status' => 'pending',
        ]);

        app(ImportService::class)->processDocs($import);

        $import->refresh();

        $this->assertSame('completed', $import->status);
        $this->assertSame('heading', $import->parsed_schema[0]['type']);
        $this->assertSame(['Red', 'Blue'], $import->parsed_schema[2]['options']);
    }

    public function test_build_schema_groups_questions_under_headings(): void
    {
        $items = [
            ['type' => 'heading', 'label' => 'Personal'],
            ['type' => 'question', 'label' => 'Full name', 'field_type' => 'text', 'options' => []],
            ['type' => 'question', 'label' => 'Color', 'field_type' => 'dropdown', 'options' => ['Red', 'Blue']],
        ];

        $schema = app(ImportService::class)->buildSchema($items, 'My Form');

        $this->assertSame('My Form', $schema['title']);
        $this->assertCount(1, $schema['steps']);
        $this->assertCount(1, $schema['steps'][0]['sections']);
        $this->assertSame('Personal', $schema['steps'][0]['sections'][0]['title']);

        $fields = $schema['steps'][0]['sections'][0]['fields'];
        $this->assertCount(2, $fields);
        $this->assertStringStartsWith('full_name', $fields[0]['key']);
        $this->assertSame('Color', $fields[1]['label']);
        $this->assertSame(['label' => 'Red', 'value' => 'red'], $fields[1]['options'][0]);
    }

    public function test_upload_parses_and_creates_form(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'exam.docx',
            file_get_contents($this->writeDocx()),
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );

        $component = Livewire::test(Docx::class)
            ->set('file', $file)
            ->call('upload');

        $import = Import::findOrFail($component->get('importId'));
        $this->assertSame('completed', $import->status);
        $this->assertNotEmpty($import->parsed_schema);

        $component
            ->assertSet('mapped', true)
            ->assertSet('title', 'exam.docx')
            ->set('title', 'My Imported Form')
            ->call('createForm')
            ->assertRedirect();

        $this->assertDatabaseHas('forms', ['title' => 'My Imported Form']);
    }
}
