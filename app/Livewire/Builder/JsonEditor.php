<?php

namespace App\Livewire\Builder;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class JsonEditor extends Component
{
    #[Reactive]
    public array $schema = [];

    public string $rawJson = '';
    public ?string $error = null;
    public bool $userEdited = false;

    public function mount()
    {
        $this->syncRaw();
    }

    public function updatedSchema()
    {
        if (!$this->userEdited) {
            $this->syncRaw();
        }
    }

    public function updatedRawJson()
    {
        if ($this->rawJson !== $this->freshJson()) {
            $this->userEdited = true;
        }
    }

    public function reload()
    {
        $this->userEdited = false;
        $this->syncRaw();
    }

    public function apply()
    {
        $decoded = $this->tryDecode($this->rawJson);

        if ($decoded === null) {
            $repaired = $this->tryRepair($this->rawJson);

            if ($repaired !== null) {
                $this->rawJson = $repaired;
                $decoded = $this->tryDecode($repaired);
            }
        }

        if ($decoded === null) {
            $this->error = 'Invalid JSON. Could not be repaired.';
            return;
        }

        if (!is_array($decoded)) {
            $this->error = 'JSON must be an object or array.';
            return;
        }

        $this->dispatch('schema-replace', schema: $decoded);
        $this->userEdited = false;
        $this->error = null;
    }

    private function syncRaw(): void
    {
        $this->rawJson = $this->freshJson();
        $this->error = null;
    }

    private function freshJson(): string
    {
        return json_encode(
            $this->schema,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) ?: '{}';
    }

    private function tryDecode(string $json): mixed
    {
        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }
    }

    private function tryRepair(string $json): ?string
    {
        $repaired = preg_replace('/,(\s*[}\]])/', '$1', $json);

        if ($repaired === null) {
            return null;
        }

        return $this->tryDecode($repaired) !== null ? $repaired : null;
    }

    public function render()
    {
        return view('livewire.builder.json-editor');
    }
}
