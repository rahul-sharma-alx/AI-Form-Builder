<?php

namespace App\Livewire\Forms;

use Livewire\Component;
use App\Models\Form;
use App\Support\SchemaFactory;

class Create extends Component
{
    public $title = "";
    public $description = "";
    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
    ];

    public function save(){
        $this->validate();
        $form = Form::create([
            'title' => $this->title,
            'description' => $this->description,
            'schema' => SchemaFactory::create($this->title),
            'status' => 'draft',
        ]);
        return redirect()->route('forms.builder', $form);
    }
    public function render()
    {
        return view('livewire.forms.create');
    }
}
