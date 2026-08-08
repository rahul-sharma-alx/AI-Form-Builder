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

    public static function edit(array $schema, string $instruction, ?string $title = null): string
    {
        $titleLine = $title !== null ? "Form title: {$title}. " : '';

        return $titleLine
            ."Modify the existing form schema below to carry out this instruction: {$instruction}. "
            .'Do NOT regenerate the schema from scratch. '
            .'Keep every unchanged step, section and field exactly as-is, preserving their "id" and "key" values. '
            .'Make the smallest possible change that fulfils the instruction. '
            .'Return ONLY the complete modified schema as JSON matching exactly this shape: '
            .'{"title":"Form title","steps":[{"id":"unique","title":"Step title","sections":[{"id":"unique","title":"Section title","fields":[{"type":"text","key":"snake_case_key","label":"Question label","required":true,"placeholder":"","help":"","options":[],"min":null,"max":null,"regex":"","validation":[]}]}]}]}. '
            .'Allowed field types: '.implode(', ', FieldTypes::all()).'. '
            .'For dropdown, radio and checkbox fields include options as [{"label":"Display text","value":"value"}]. '
            .'Existing form schema to modify: '.json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
