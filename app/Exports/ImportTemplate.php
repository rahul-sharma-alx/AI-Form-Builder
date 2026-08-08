<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ImportTemplate implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['type', 'label', 'required', 'placeholder', 'help', 'options', 'section', 'validation'];
    }

    public function array(): array
    {
        return [
            ['text', 'Full name', 'yes', 'Enter your full name', 'Your legal name', '', 'Personal details', 'max:100'],
            ['dropdown', 'Favorite color', 'no', '', '', 'Red|Blue|Green', 'Preferences', 'required'],
            ['rating', 'Overall satisfaction', 'yes', '', '', '', 'Feedback', 'min:1|max:5'],
        ];
    }
}
