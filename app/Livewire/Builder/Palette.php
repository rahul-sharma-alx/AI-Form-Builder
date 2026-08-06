<?php

namespace App\Livewire\Builder;

use Livewire\Component;
use App\Support\FieldTypes;

class Palette extends Component
{
    public function add(string $type)
    {
        $this->dispatch('field-add', type: $type);
    }

    public function render()
    {
        return view('livewire.builder.palette', [
            'fieldTypes' => FieldTypes::all(),
        ]);
    }
}
