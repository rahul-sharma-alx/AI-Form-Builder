<?php

namespace App\Support;

class FieldTypes
{
    public static function all(): array
    {
        return [

            'text',
            'textarea',
            'number',
            'email',
            'phone',
            'date',
            'dropdown',
            'radio',
            'checkbox',
            'file',
            'heading',
            'rating',
            'section',
            'html',
        ];
    }
}
