<?php

namespace App\Livewire\Forms;

use App\Models\Form;
use App\Support\SchemaFactory;
use Livewire\Component;

class Create extends Component
{
    public $title = '';

    public $description = '';

    public $templateId = '';

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
    ];

    public function save()
    {
        $this->validate();

        $form = Form::create([
            'title' => $this->title,
            'description' => $this->description,
            'schema' => $this->templateSchema() ?? SchemaFactory::create($this->title),
            'status' => 'draft',
        ]);

        return redirect()->route('forms.builder', $form);
    }

    public function templates(): array
    {
        return config('form_templates', []);
    }

    protected function templateSchema(): ?array
    {
        foreach (config('form_templates', []) as $template) {
            if (($template['id'] ?? null) === $this->templateId) {
                $schema = $template['schema'] ?? null;

                if (is_array($schema)) {
                    $schema['title'] = $this->title;
                }

                return $schema;
            }
        }

        return null;
    }

    public function render()
    {
        return view('livewire.forms.create');
    }
}
