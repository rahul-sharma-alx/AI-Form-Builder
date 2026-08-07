<?php

namespace App\Livewire\Public;

use App\Models\Form;
use App\Services\SubmissionService;
use App\Support\SchemaFields;
use Livewire\Component;

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
                $rules['answers.'.$key] = [
                    empty($field['required']) ? 'nullable' : 'required',
                    'array',
                ];

                $values = array_column($field['options'] ?? [], 'value');
                if ($values) {
                    $rules['answers.'.$key.'.*'] = 'in:'.implode(',', array_map('strval', $values));
                }

                continue;
            }

            $rules['answers.'.$key] = $this->rulesFor($field);
        }

        return $rules;
    }

    public function submit()
    {
        $this->validate();

        app(SubmissionService::class)->store($this->form, $this->answers);

        $this->submitted = true;
    }

    protected function rulesFor(array $field): array
    {
        $rules = [];

        if (! empty($field['required'])) {
            $rules[] = 'required';
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
                    $rules[] = 'min:'.$field['min'];
                }
                if (isset($field['max']) && $field['max'] !== null) {
                    $rules[] = 'max:'.$field['max'];
                }
                break;

            case 'date':
                $rules[] = 'date';
                break;

            case 'dropdown':
            case 'radio':
                $values = array_column($field['options'] ?? [], 'value');
                if ($values) {
                    $rules[] = 'in:'.implode(',', array_map('strval', $values));
                }
                break;

            case 'checkbox':
                break;
        }

        if (! empty($field['regex']) && in_array($field['type'], ['text', 'textarea', 'email', 'phone'], true)) {
            $rules[] = 'regex:'.$field['regex'];
        }

        if (in_array($field['type'], ['text', 'textarea'], true)) {
            if (isset($field['min']) && $field['min'] !== null) {
                $rules[] = 'min:'.$field['min'];
            }
            if (isset($field['max']) && $field['max'] !== null) {
                $rules[] = 'max:'.$field['max'];
            }
        }

        foreach ($this->parseValidationRules($field) as $rule) {
            $rules[] = $rule;
        }

        return $rules;
    }

    protected function parseValidationRules(array $field): array
    {
        $raw = $field['validation'] ?? [];

        if (is_string($raw)) {
            $raw = explode('|', $raw);
        }

        $skip = ['required', 'nullable', 'sometimes'];

        return array_filter(array_map('trim', $raw), function ($rule) use ($skip) {
            if ($rule === '') {
                return false;
            }

            $name = explode(':', $rule)[0];

            return ! in_array($name, $skip, true);
        });
    }

    protected function answerFields(): array
    {
        return SchemaFields::answerable($this->form->schema);
    }

    public function render()
    {
        return view('livewire.public.fill');
    }
}
