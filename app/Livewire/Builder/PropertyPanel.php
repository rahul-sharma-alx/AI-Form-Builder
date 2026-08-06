<?php

namespace App\Livewire\Builder;

use Livewire\Attributes\On;
use Livewire\Component;

class PropertyPanel extends Component
{
    public array $field = [];

    public function updatedField()
    {
        $this->dispatch('field-update', field: $this->field);
    }

    #[On('field-selected')]
    public function onFieldSelected(?array $field)
    {
        $this->field = $field ?? [];
    }

    public function addOption()
    {
        $this->field['options'][] = ['label' => '', 'value' => ''];
        $this->dispatch('field-update', field: $this->field);
    }

    public function removeOption(int $index)
    {
        if (!isset($this->field['options'][$index])) {
            return;
        }

        unset($this->field['options'][$index]);

        $this->field['options'] = array_values($this->field['options']);

        $this->dispatch('field-update', field: $this->field);
    }

    public function render()
    {
        return view('livewire.builder.property-panel');
    }
}
