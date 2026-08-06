<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Form;

class Fill extends Component
{
    public Form $form;
    public array $answers = [];
    public bool $submitted = false;

    public function mount(Form $form)
    {
        foreach ($this->answerFields() as $key => $field) {
            $default = $field['default'] ?? null;

            if ($field['type'] === 'checkbox' && is_array($default)) {
                $this->answers[$key] = array_values($default);
            } elseif ($default !== null && $default !== '') {
                $this->answers[$key] = $default;
            } else {
                $this->answers[$key] = null;
            }
        }
    }

    public function steps(): array
    {
        return $this->form->schema['steps'] ?? [];
    }

    public function rules(): array
    {
        $rules = [];

        foreach ($this->answerFields() as $key => $field) {
            if ($field['type'] === 'checkbox') {
                $rules['answers.' . $key] = [
                    empty($field['required']) ? 'nullable' : 'required',
                    'array',
                ];

                $values = array_column($field['options'] ?? [], 'value');
                if ($values) {
                    $rules['answers.' . $key . '.*'] = 'in:' . implode(',', array_map('strval', $values));
                }

                continue;
            }

            $rules['answers.' . $key] = $this->rulesFor($field);
        }

        return $rules;
    }

    public function submit()
    {
        $this->validate();

        $this->submitted = true;
    }

    protected function rulesFor(array $field): array
    {
        $rules = [];

        if (! empty($field['required'])) {
            $rules[] = $field['type'] === 'checkbox' ? 'required' : 'required';
        } else {
            $rules[] = 'nullable';
        }

        switch ($field['type']) {
            case 'email':
                $rules[] = 'email';
                break;

            case 'phone':
                $rules[] = 'regex:/^[0-9()+\-\s\.]+$/';
                break;

            case 'number':
            case 'rating':
                $rules[] = 'numeric';
                if (isset($field['min']) && $field['min'] !== null) {
                    $rules[] = 'min:' . $field['min'];
                }
                if (isset($field['max']) && $field['max'] !== null) {
                    $rules[] = 'max:' . $field['max'];
                }
                break;

            case 'date':
                $rules[] = 'date';
                break;

            case 'dropdown':
            case 'radio':
                $values = array_column($field['options'] ?? [], 'value');
                if ($values) {
                    $rules[] = 'in:' . implode(',', array_map('strval', $values));
                }
                break;

            case 'checkbox':
                break;
        }

        if (! empty($field['regex']) && in_array($field['type'], ['text', 'textarea', 'email', 'phone'], true)) {
            $rules[] = 'regex:' . $field['regex'];
        }

        return $rules;
    }

    protected function answerFields(): array
    {
        $fields = [];

        foreach ($this->steps() as $step) {
            foreach ($step['sections'] ?? [] as $section) {
                foreach ($section['fields'] ?? [] as $field) {
                    if (in_array($field['type'], ['heading', 'section', 'file'], true)) {
                        continue;
                    }

                    $fields[$field['key']] = $field;
                }
            }
        }

        return $fields;
    }

    public function render()
    {
        return view('livewire.public.fill');
    }
}