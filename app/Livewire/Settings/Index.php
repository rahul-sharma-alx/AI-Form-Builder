<?php

namespace App\Livewire\Settings;

use App\AI\Providers\GeminiProvider;
use App\AI\Providers\OpenAIProvider;
use App\AI\Providers\OpenRouterProvider;
use App\Support\EnvEditor;
use Livewire\Component;

class Index extends Component
{
    public string $provider = 'openrouter';

    public string $apiKey = '';

    public string $model = '';

    public bool $saving = false;

    public bool $tested = false;

    public bool $connected = false;

    public ?string $message = null;

    protected array $providers = [
        'openai' => ['name' => 'OpenAI', 'class' => OpenAIProvider::class, 'env' => 'OPENAI_API_KEY'],
        'gemini' => ['name' => 'Google Gemini', 'class' => GeminiProvider::class, 'env' => 'GEMINI_API_KEY'],
        'openrouter' => ['name' => 'OpenRouter', 'class' => OpenRouterProvider::class, 'env' => 'OPENROUTER_API_KEY'],
    ];

    public function mount(): void
    {
        $this->provider = $this->providerFromConfig();
        $this->model = (string) config('services.ai.model');
        $this->apiKey = (string) $this->currentKey();
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'in:openai,gemini,openrouter'],
            'model' => ['required', 'string', 'max:255'],
            'apiKey' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->saving = true;
        $this->tested = false;

        $provider = $this->providers[$this->provider];
        $key = trim($this->apiKey) !== '' ? trim($this->apiKey) : (string) $this->currentKey();

        if ($key === '') {
            $this->saving = false;
            $this->tested = true;
            $this->connected = false;
            $this->message = 'An API key is required. Paste your key or set it in .env.';

            return;
        }

        $class = $provider['class'];
        $model = trim($this->model);

        EnvEditor::set([
            'AI_PROVIDER' => $class,
            'AI_MODEL' => $model,
            $provider['env'] => $key,
        ]);

        config()->set('services.ai.provider', $class);
        config()->set('services.ai.model', $model);
        config()->set('services.'.$this->provider.'.api_key', $key);

        $this->saving = false;
        $this->tested = true;

        try {
            app($class)->complete('Reply with exactly this JSON: {"ok":true}', $model);
            $this->connected = true;
            $this->message = 'Connection successful — AI API saved to .env.';
        } catch (\Throwable $e) {
            $this->connected = false;
            $this->message = 'Connection failed — check your API key and model name: '.$e->getMessage();
        }
    }

    protected function providerFromConfig(): string
    {
        $class = config('services.ai.provider');

        foreach ($this->providers as $slug => $provider) {
            if ($provider['class'] === $class) {
                return $slug;
            }
        }

        return 'openrouter';
    }

    protected function currentKey(): ?string
    {
        return config('services.'.$this->provider.'.api_key') ?: null;
    }

    public function providerOptions(): array
    {
        return $this->providers;
    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}
