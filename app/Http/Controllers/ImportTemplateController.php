<?php

namespace App\Http\Controllers;

use App\Exports\ImportTemplate;
use Maatwebsite\Excel\Facades\Excel;

class ImportTemplateController extends Controller
{
    public function xlsx()
    {
        return Excel::download(new ImportTemplate, 'form-import-template.xlsx');
    }
}
