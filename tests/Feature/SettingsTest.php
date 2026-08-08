<?php

namespace Tests\Feature;

use App\AI\Providers\OpenAIProvider;
use App\Livewire\Settings\Index;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    public function test_settings_page_renders(): void
    {
        $this->get('/settings')->assertOk();
    }

    public function test_mount_uses_configured_provider_and_model(): void
    {
        config()->set('services.ai.provider', OpenAIProvider::class);
        config()->set('services.ai.model', 'gpt-test');

        Livewire::test(Index::class)
            ->assertSet('provider', 'openai')
            ->assertSet('model', 'gpt-test');
    }

    public function test_save_requires_provider_model_and_key(): void
    {
        Livewire::test(Index::class)
            ->set('provider', '')
            ->set('model', '')
            ->set('apiKey', '')
            ->call('save')
            ->assertHasErrors(['provider', 'model']);
    }

    public function test_save_rejects_unknown_provider(): void
    {
        Livewire::test(Index::class)
            ->set('provider', 'anthropic')
            ->set('model', 'claude-3')
            ->call('save')
            ->assertHasErrors(['provider']);
    }
}
