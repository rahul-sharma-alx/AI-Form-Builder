<?php

namespace App\Support;

class AiPromptBuilder
{
    public static function generate(string $description, ?string $title = null): string
    {
        $titleLine = $title !== null ? "Form title: {$title}. " : '';

        return $titleLine
            ."Build a complete form schema in JSON for this purpose: {$description}. "
            .'The JSON must match exactly this shape: '
            .'{"title":"Form title","steps":[{"id":"unique","title":"Step title","sections":[{"id":"unique","title":"Section title","fields":[{"type":"text","key":"snake_case_key","label":"Question label","required":true,"placeholder":"","help":"","options":[],"min":null,"max":null,"regex":"","validation":[]}]}]}]}. '
            .'Allowed field types: '.implode(', ', FieldTypes::all()).'. '
            .'For dropdown, radio and checkbox fields include options as [{"label":"Display text","value":"value"}]. '
            .'Use unique lowercase snake_case keys, e.g. full_name. '
            .'Prefer multiple steps for multi-part forms and group related questions into sections.';
    }
}
