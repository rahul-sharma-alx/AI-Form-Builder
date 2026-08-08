<?php

namespace Tests\Feature;

use App\AI\Providers\OpenRouterProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenRouterProviderTest extends TestCase
{
    public function test_complete_posts_to_openrouter_with_api_key_and_body(): void
    {
        Http::fake([
            'openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => '{"ok":true}']]]]),
        ]);

        config()->set('services.openrouter.api_key', 'sk-test-123');
        config()->set('services.openrouter.url', 'https://openrouter.ai/api/v1/chat/completions');

        $result = app(OpenRouterProvider::class)->complete('Make a form', 'openai/gpt-4o-mini');

        $this->assertSame('{"ok":true}', $result);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://openrouter.ai/api/v1/chat/completions'
                && str_contains($request->header('Authorization')[0] ?? '', 'sk-test-123')
                && $request->data()['model'] === 'openai/gpt-4o-mini'
                && $request->data()['messages'][0]['role'] === 'system';
        });
    }

    public function test_complete_throws_when_content_missing(): void
    {
        Http::fake([
            'openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => null]]]]),
        ]);

        $this->expectException(\RuntimeException::class);

        app(OpenRouterProvider::class)->complete('Make a form', 'openai/gpt-4o-mini');
    }
}
