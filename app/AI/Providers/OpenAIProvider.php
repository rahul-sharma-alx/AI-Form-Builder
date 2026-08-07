<?php

namespace App\AI\Providers;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAIProvider implements ProviderInterface
{
    public function complete(string $prompt, string $model): string
    {
        $response = Http::withToken(config('services.openai.api_key'))
            ->acceptJson()
            ->timeout(120)
            ->post(config('services.openai.url'), [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Return only valid JSON. Do not use markdown code fences.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
            ]);

        $response->throw();

        $content = $response->json('choices.0.message.content');

        if (! is_string($content)) {
            throw new RuntimeException('AI provider returned no content.');
        }

        return $content;
    }
}
