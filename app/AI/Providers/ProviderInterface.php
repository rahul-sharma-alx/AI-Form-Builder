<?php

namespace App\AI\Providers;

interface ProviderInterface
{
    public function complete(string $prompt, string $model): string;
}
