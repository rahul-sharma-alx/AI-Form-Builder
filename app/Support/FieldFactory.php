<?php

namespace App\Support;
use Illuminate\Support\Str;
class FieldFactory
{
    public static function make(string $type): array
    {
        $id = Str::uuid()->toString();

        $label = ucfirst($type);

        return [
            'id' => $id,
            'type' => $type,
            'key' => self::generateKey($label, $id),
            'label' => $label,
            'placeholder' => "",
            'help' => "",
            'default' => null,
            'required' => false,
            'options' => [],
            'min' => null,
            'max' => null,
            'regex' => "",
            'validation' => [],
       ];
    }

    public static function generateKey(string $label, string $uuid): string
    {
        $base = Str::slug($label, '_');

        $base = $base !== '' ? $base : 'field';

        return $base . '_' . Str::substr(str_replace('-', '', $uuid), 0, 8);
    }
}
