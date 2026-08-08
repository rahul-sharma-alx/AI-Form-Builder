<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Forms\Index;
use App\Livewire\Forms\Create;
use App\Livewire\Forms\Builder;
use App\Livewire\Forms\Versions;
use App\Livewire\Public\Fill;
use App\Livewire\Submissions\Index as Submissions;
use App\Livewire\Ai\Generate as AiGenerate;
use App\Livewire\Ai\Edit as AiEdit;
use App\Livewire\Imports\Docx as DocxImport;
use App\Livewire\Imports\Excel as ExcelImport;
use App\Livewire\Settings\Index as Settings;
use App\Http\Controllers\ImportTemplateController;

Route::get('/', fn () => redirect()->route('forms.index'));

Route::get('/forms', Index::class)->name('forms.index');
Route::get('/forms/create', Create::class)->name('forms.create');
Route::get('/forms/{form}/builder', Builder::class)->name('forms.builder');
Route::get('/forms/{form}/versions', Versions::class)->name('forms.versions');
Route::get('/forms/{form}/submissions', Submissions::class)->name('forms.submissions');
Route::get('/forms/{form}/ai', AiGenerate::class)->name('forms.ai');
Route::get('/forms/{form}/ai/edit', AiEdit::class)->name('forms.ai.edit');
Route::get('/imports/docx', DocxImport::class)->name('imports.docx');
Route::get('/imports/xlsx', ExcelImport::class)->name('imports.xlsx');
Route::get('/imports/xlsx/template', [ImportTemplateController::class, 'xlsx'])->name('imports.xlsx.template');
Route::get('/settings', Settings::class)->name('settings.index');
Route::get('/forms/{form}/public', Fill::class)->name('forms.public');
Route::delete('/forms/{form}', Index::class)->name('forms.destroy');
