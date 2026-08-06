<?php

namespace App\Support;
use Illuminate\Support\Str;

class SchemaFactory
{
    public static function create(string $title): array
    {
        return [
            'title' => $title,
            'steps' => [
                [
                    'id' => Str::uuid()->toString(),
                    'title' => 'Step 1',
                    'sections' => [
                        [
                            'id' => Str::uuid()->toString(),
                            'title' => 'Section 1',
                            'fields' => [],
                        ]
                    ],
                ]
            ]
        ];
    }
}
