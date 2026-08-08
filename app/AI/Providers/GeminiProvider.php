<?php

namespace App\AI\Providers;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProvider implements ProviderInterface
{
    public function complete(string $prompt, string $model): string
    {
        $response = Http::withQueryParameters(['key' => config('services.gemini.api_key')])
            ->acceptJson()
            ->timeout(120)
            ->post(config('services.gemini.url').'/'.$model.':generateContent', [
                'contents' => [
                    ['parts' => [['text' => "Return only valid JSON. Do not use markdown code fences.\n\n".$prompt]]],
                ],
                'generationConfig' => ['temperature' => 0.2],
            ]);

        $response->throw();

        $content = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($content)) {
            throw new RuntimeException('AI provider returned no content.');
        }

        return $content;
    }
}
