<?php

namespace App\Support;

use Illuminate\Support\Facades\Validator;
use Throwable;

class ValidationRules
{
    public static function check(mixed $rules): array
    {
        if (is_array($rules)) {
            $rules = implode('|', array_filter($rules));
        }

        if (! is_string($rules) || trim($rules) === '') {
            return [true, null];
        }

        try {
            Validator::make(['v' => 'x'], ['v' => $rules])->passes();

            return [true, null];
        } catch (Throwable $e) {
            return [false, $e->getMessage()];
        }
    }

    public static function sanitize(mixed $rules): string
    {
        if (is_array($rules)) {
            $rules = implode('|', array_filter($rules));
        }

        if (! is_string($rules) || trim($rules) === '') {
            return '';
        }

        $valid = [];

        foreach (explode('|', $rules) as $rule) {
            $rule = trim($rule);

            if ($rule === '') {
                continue;
            }

            try {
                Validator::make(['v' => 'x'], ['v' => $rule])->passes();
                $valid[] = $rule;
            } catch (Throwable) {
                // Skip invalid rule token.
            }
        }

        return implode('|', $valid);
    }
}
