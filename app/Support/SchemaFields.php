<?php

namespace App\Support;

class SchemaFields
{
    public static function answerable(array $schema): array
    {
        $fields = [];

        foreach ($schema['steps'] ?? [] as $step) {
            foreach ($step['sections'] ?? [] as $section) {
                foreach ($section['fields'] ?? [] as $field) {
                    if (in_array($field['type'], ['heading', 'section', 'html', 'file'], true)) {
                        continue;
                    }

                    $fields[$field['key']] = $field;
                }
            }
        }

        return $fields;
    }
}
