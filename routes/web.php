<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Forms\Index;
use App\Livewire\Forms\Create;
use App\Livewire\Forms\Builder;

Route::get('/', fn () => redirect()->route('forms.index'));

Route::get('/forms', Index::class)->name('forms.index');
Route::get('/forms/create', Create::class)->name('forms.create');
Route::get('/forms/{form}/builder', Builder::class)->name('forms.builder');
Route::delete('/forms/{form}', Index::class)->name('forms.destroy');
