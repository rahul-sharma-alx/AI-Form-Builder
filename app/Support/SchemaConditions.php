<?php

namespace App\Support;

class SchemaConditions
{
    public static function visible(?array $visibility, array $answers): bool
    {
        if (empty($visibility['field'])) {
            return true;
        }

        $field = $visibility['field'];
        $op = $visibility['op'] ?? 'equals';
        $expected = $visibility['value'] ?? null;
        $actual = $answers[$field] ?? null;

        switch ($op) {
            case 'not_equals':
                return $actual != $expected;

            case 'empty':
                return self::isEmpty($actual);

            case 'not_empty':
                return ! self::isEmpty($actual);

            default:
                return $actual == $expected;
        }
    }

    public static function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [] || $value === false;
    }
}
