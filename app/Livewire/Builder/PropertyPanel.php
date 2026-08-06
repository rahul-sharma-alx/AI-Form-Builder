<?php

namespace App\Livewire\Builder;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Support\ValidationRules;

class PropertyPanel extends Component
{
    public array $field = [];
    public ?string $lastValidValidation = null;
    public string $validationError = '';

    public function updatedField()
    {
        [$ok, $error] = ValidationRules::check($this->field['validation'] ?? null);

        if ($ok) {
            $this->validationError = '';
            $this->lastValidValidation = $this->validationString();
            $this->dispatch('field-update', field: $this->field);

            return;
        }

        $this->validationError = $error ?? 'Invalid validation rule(s).';

        $payload = $this->field;
        $payload['validation'] = $this->lastValidValidation;

        $this->dispatch('field-update', field: $payload);
    }

    protected function validationString(): ?string
    {
        $v = $this->field['validation'] ?? null;

        if (is_array($v)) {
            $v = implode('|', array_filter($v));
        }

        return is_string($v) && $v !== '' ? $v : null;
    }

    #[On('field-selected')]
    public function onFieldSelected(?array $field)
    {
        $this->field = $field ?? [];
        $this->lastValidValidation = $this->validationString();
        $this->validationError = '';
    }

    public function addOption()
    {
        $this->field['options'][] = ['label' => '', 'value' => ''];
        $this->updatedField();
    }

    public function removeOption(int $index)
    {
        if (!isset($this->field['options'][$index])) {
            return;
        }

        unset($this->field['options'][$index]);

        $this->field['options'] = array_values($this->field['options']);

        $this->updatedField();
    }

    public function render()
    {
        return view('livewire.builder.property-panel');
    }
}
