<?php

namespace Tests\Unit;

use App\Support\EnvEditor;
use PHPUnit\Framework\TestCase;

class EnvEditorTest extends TestCase
{
    public function test_replaces_existing_key_in_place(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($file, "AI_PROVIDER=old\nAI_MODEL=old-model\n");

        EnvEditor::set(['AI_MODEL' => 'gpt-4o'], $file);

        $content = file_get_contents($file);
        $this->assertStringContainsString('AI_PROVIDER=old', $content);
        $this->assertStringContainsString('AI_MODEL=gpt-4o', $content);
        $this->assertSame(1, substr_count($content, 'AI_MODEL='));

        unlink($file);
    }

    public function test_appends_new_key(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($file, "APP_NAME=x\n");

        EnvEditor::set(['GEMINI_API_KEY' => 'abc-123'], $file);

        $this->assertStringContainsString('GEMINI_API_KEY=abc-123', file_get_contents($file));

        unlink($file);
    }

    public function test_quotes_values_with_spaces(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($file, "APP_NAME=x\n");

        EnvEditor::set(['AI_MODEL' => 'my model name'], $file);

        $this->assertStringContainsString('AI_MODEL="my model name"', file_get_contents($file));

        unlink($file);
    }

    public function test_keeps_backslashes_in_provider_class(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($file, "APP_NAME=x\n");

        EnvEditor::set(['AI_PROVIDER' => 'App\\AI\\Providers\\GeminiProvider'], $file);

        $this->assertStringContainsString('AI_PROVIDER=App\AI\Providers\GeminiProvider', file_get_contents($file));

        unlink($file);
    }

    public function test_does_not_duplicate_key(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($file, "AI_PROVIDER=one\nAI_PROVIDER=two\n");

        EnvEditor::set(['AI_PROVIDER' => 'three'], $file);

        $content = file_get_contents($file);
        $this->assertSame(1, substr_count($content, 'AI_PROVIDER='));
        $this->assertStringContainsString('AI_PROVIDER=three', $content);

        unlink($file);
    }
}
