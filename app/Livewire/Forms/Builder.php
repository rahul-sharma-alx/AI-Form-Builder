<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Form;
use App\Services\FormService;
use App\Support\FieldFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Builder extends Component
{
    public Form $form;
    public array $schema = [];
    public ?string $currentStepId = null;
    public ?string $currentSectionId = null;
    public ?string $selectedFieldId = null;
    public array $selectedField = [];

    public string $title = '';
    public ?string $description = null;
    public array $settings = [];
    public array $metadata = [];
    public int $version = 1;
    public bool $dirty = false;
    public string $savedAt = '';

    public function boot(FormService $forms): void
    {
        $this->forms = $forms;
    }

    public function mount(Form $form)
    {
        $this->form = $form;

        $this->schema = $this->normalizeSchema($form->schema ?? []);

        $this->currentStepId = $this->schema['steps'][0]['id'] ?? null;

        $this->currentSectionId = $this->schema['steps'][0]['sections'][0]['id'] ?? null;

        $this->title = $form->title ?? '';
        $this->description = $form->description;
        $this->settings = $form->settings ?? [];
        $this->metadata = $form->metadata ?? [];
        $this->version = (int) ($form->version ?? 1);
    }

    public function autosave(): void
    {
        $this->forms->autosave($this->form, $this->payload());
        $this->dirty = false;
        $this->version = (int) $this->form->version;
        $this->savedAt = Carbon::parse($this->form->last_saved_at)->format('H:i:s');
    }

    public function publish()
    {
        $this->forms->publish($this->form);
        session()->flash('success', 'Form published.');
    }

    public function unpublish()
    {
        $this->forms->unpublish($this->form);
        session()->flash('success', 'Form saved as draft.');
    }

    public function save()
    {
        $this->forms->save($this->form, $this->payload());
        $this->dirty = false;
        $this->version = (int) $this->form->version;
    }

    private function payload(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'schema' => $this->schema,
            'settings' => $this->settings,
            'metadata' => $this->metadata,
        ];
    }

    private function markDirty(): void
    {
        $this->dirty = true;
        $this->dispatch('content-dirty');
    }

    private function normalizeSchema(array $schema): array
    {
        $schema['title'] = $schema['title'] ?? $this->title;

        if (!empty($schema['steps'])) {
            return $schema;
        }

        // Legacy shape: {title, sections} → wrap into a single step.
        $schema['steps'] = [[
            'id' => Str::uuid()->toString(),
            'title' => 'Step 1',
            'sections' => $schema['sections'] ?? [
                [
                    'id' => Str::uuid()->toString(),
                    'title' => 'Section 1',
                    'fields' => [],
                ]
            ],
        ]];

        unset($schema['sections']);

        return $schema;
    }

    private function locate(?string $stepId, ?string $sectionId): array
    {
        foreach ($this->schema['steps'] as $stepIndex => $step) {
            if ($step['id'] === $stepId) {
                foreach ($step['sections'] as $sectionIndex => $section) {
                    if ($section['id'] === $sectionId) {
                        return [$stepIndex, $sectionIndex];
                    }
                }
            }
        }

        return [0, 0];
    }

    #[On('step-selected')]
    public function setCurrentStep(string $id)
    {
        $this->currentStepId = $id;

        foreach ($this->schema['steps'] as $step) {
            if ($step['id'] === $id) {
                $this->currentSectionId = $step['sections'][0]['id'] ?? null;
                return;
            }
        }
    }

    #[On('step-add')]
    public function addStep()
    {
        $stepId = Str::uuid()->toString();

        $this->schema['steps'][] = [
            'id' => $stepId,
            'title' => 'Step ' . (count($this->schema['steps']) + 1),
            'sections' => [
                [
                    'id' => Str::uuid()->toString(),
                    'title' => 'Section 1',
                    'fields' => [],
                ]
            ],
        ];

        $lastStep = $this->schema['steps'][array_key_last($this->schema['steps'])];

        $this->currentStepId = $lastStep['id'];
        $this->currentSectionId = $lastStep['sections'][0]['id'];

        $this->markDirty();
    }

    #[On('section-selected')]
    public function setCurrentSection(string $id)
    {
        $this->currentSectionId = $id;
    }

    #[On('section-add')]
    public function addSection()
    {
        [$stepIndex] = $this->locate($this->currentStepId, $this->currentSectionId);

        $sectionId = Str::uuid()->toString();

        $count = count($this->schema['steps'][$stepIndex]['sections']);

        $this->schema['steps'][$stepIndex]['sections'][] = [
            'id' => $sectionId,
            'title' => 'Section ' . ($count + 1),
            'fields' => [],
        ];

        $this->currentSectionId = $sectionId;

        $this->markDirty();
    }

    #[On('field-add')]
    public function addField(string $type)
    {
        [$stepIndex, $sectionIndex] = $this->locate($this->currentStepId, $this->currentSectionId);

        $field = FieldFactory::make($type);

        $this->schema['steps'][$stepIndex]['sections'][$sectionIndex]['fields'][] = $field;

        $this->selectedFieldId = $field['id'];
        $this->selectedField = $field;
        $this->dispatch('field-selected', field: $field);

        $this->markDirty();
    }

    #[On('field-select')]
    public function selectField(string $id)
    {
        foreach ($this->schema['steps'] as $step) {
            foreach ($step['sections'] as $section) {
                foreach ($section['fields'] as $field) {
                    if ($field['id'] === $id) {
                        $this->selectedFieldId = $id;
                        $this->selectedField = $field;
                        $this->currentStepId = $step['id'];
                        $this->currentSectionId = $section['id'];
                        $this->dispatch('field-selected', field: $field);
                        return;
                    }
                }
            }
        }
    }

    #[On('field-update')]
    public function updateField(array $field)
    {
        $this->selectedField = $field;

        foreach ($this->schema['steps'] as &$step) {
            foreach ($step['sections'] as &$section) {
                foreach ($section['fields'] as &$existing) {
                    if ($existing['id'] === $field['id']) {
                        $existing = $field;
                        $this->markDirty();
                        return;
                    }
                }
            }
        }
    }

    #[On('field-duplicate')]
    public function duplicateField(string $id)
    {
        foreach ($this->schema['steps'] as &$step) {
            foreach ($step['sections'] as &$section) {
                foreach ($section['fields'] as $index => $field) {
                    if ($field['id'] === $id) {
                        $copy = $field;
                        $copy['id'] = Str::uuid()->toString();
                        array_splice($section['fields'], $index + 1, 0, [$copy]);
                        $this->markDirty();
                        return;
                    }
                }
            }
        }
    }

    #[On('field-delete')]
    public function deleteField(string $id)
    {
        foreach ($this->schema['steps'] as &$step) {
            foreach ($step['sections'] as &$section) {
                foreach ($section['fields'] as $index => $field) {
                    if ($field['id'] === $id) {
                        unset($section['fields'][$index]);
                        $section['fields'] = array_values($section['fields']);
                        if ($this->selectedFieldId === $id) {
                            $this->selectedFieldId = null;
                            $this->selectedField = [];
                            $this->dispatch('field-selected', field: null);
                        }
                        $this->markDirty();
                        return;
                    }
                }
            }
        }
    }

    #[On('steps-reorder')]
    public function reorderSteps(array $ids)
    {
        $this->schema['steps'] = $this->reorderByIds($this->schema['steps'], $ids);
        $this->markDirty();
    }

    #[On('sections-reorder')]
    public function reorderSections(array $ids)
    {
        foreach ($this->schema['steps'] as &$step) {
            if ($step['id'] === $this->currentStepId) {
                $step['sections'] = $this->reorderByIds($step['sections'], $ids);
                $this->markDirty();
                return;
            }
        }
    }

    #[On('fields-reorder')]
    public function reorderFields(array $ids)
    {
        foreach ($this->schema['steps'] as &$step) {
            foreach ($step['sections'] as &$section) {
                if ($section['id'] === $this->currentSectionId) {
                    $section['fields'] = $this->reorderByIds($section['fields'], $ids);
                    $this->markDirty();
                    return;
                }
            }
        }
    }

    private function reorderByIds(array $items, array $ids): array
    {
        $byId = [];
        foreach ($items as $item) {
            $byId[$item['id']] = $item;
        }

        $reordered = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $reordered[] = $byId[$id];
                unset($byId[$id]);
            }
        }

        foreach ($byId as $leftover) {
            $reordered[] = $leftover;
        }

        return $reordered;
    }

    #[On('schema-replace')]
    public function replaceSchema(array $schema)
    {
        $this->schema = $this->normalizeSchema($schema);

        $this->currentStepId = $this->schema['steps'][0]['id'] ?? null;

        $this->currentSectionId = $this->schema['steps'][0]['sections'][0]['id'] ?? null;

        $this->selectedFieldId = null;
        $this->selectedField = [];

        $this->dispatch('field-selected', field: null);

        $this->markDirty();
    }

    public function render()
    {
        return view('livewire.forms.builder');
    }
}