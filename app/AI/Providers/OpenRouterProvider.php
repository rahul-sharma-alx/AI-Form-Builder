<?php

namespace App\AI\Providers;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenRouterProvider implements ProviderInterface
{
    public function complete(string $prompt, string $model): string
    {
        $response = Http::withToken(config('services.openrouter.api_key'))
            ->withHeaders([
                'HTTP-Referer' => config('services.openrouter.http_referer', config('app.url')),
                'X-Title' => config('services.openrouter.site_title', config('app.name')),
            ])
            ->acceptJson()
            ->timeout(120)
            ->post(config('services.openrouter.url'), [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Return only valid JSON. Do not use markdown code fences.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
            ]);

        $response->throw();

        $content = $response->json('choices.0.message.content');

        if (! is_string($content)) {
            throw new RuntimeException('AI provider returned no content.');
        }

        return $content;
    }
}
