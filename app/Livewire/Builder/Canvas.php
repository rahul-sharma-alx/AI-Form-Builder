<?php

namespace App\Livewire\Builder;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class Canvas extends Component
{
    #[Reactive]
    public array $schema = [];

    #[Reactive]
    public ?string $currentStepId = null;

    #[Reactive]
    public ?string $currentSectionId = null;

    #[Reactive]
    public ?string $selectedFieldId = null;

    public function selectStep(string $id)
    {
        $this->dispatch('step-selected', id: $id);
    }

    public function addStep()
    {
        $this->dispatch('step-add');
    }

    public function selectSection(string $id)
    {
        $this->dispatch('section-selected', id: $id);
    }

    public function addSection()
    {
        $this->dispatch('section-add');
    }

    public function select(string $id)
    {
        $this->dispatch('field-select', id: $id);
    }

    public function duplicate(string $id)
    {
        $this->dispatch('field-duplicate', id: $id);
    }

    public function delete(string $id)
    {
        $this->dispatch('field-delete', id: $id);
    }

    public function render()
    {
        return view('livewire.builder.canvas');
    }
}