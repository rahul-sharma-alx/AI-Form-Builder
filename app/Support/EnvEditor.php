<?php

namespace App\Support;

class EnvEditor
{
    /**
     * Set one or more KEY=VALUE pairs in the .env file. Existing keys are
     * replaced in place; new keys are appended.
     */
    public static function set(array $values, ?string $path = null): void
    {
        $path ??= base_path('.env');

        $content = file_get_contents($path);

        foreach ($values as $key => $value) {
            $content = self::setKey($content, $key, $value);
        }

        file_put_contents($path, $content);
    }

    private static function setKey(string $content, string $key, mixed $value): string
    {
        $line = $key.'='.self::format($value);
        $eol = str_contains($content, "\r\n") ? "\r\n" : "\n";

        $pattern = '/^'.preg_quote($key, '/').'=/';
        $found = false;
        $out = [];

        foreach (preg_split('/\r\n|\r|\n/', $content) as $l) {
            if (preg_match($pattern, $l)) {
                if (! $found) {
                    $out[] = $line;
                    $found = true;
                }

                continue;
            }
            $out[] = $l;
        }

        if (! $found) {
            $out[] = $line;
        }

        return implode($eol, $out);
    }

    private static function format(mixed $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/[\s#]/', $value)) {
            $value = '"'.str_replace('"', '\\"', $value).'"';
        }

        return $value;
    }
}
