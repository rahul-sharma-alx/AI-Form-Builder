<?php

namespace App\Livewire\Forms;

use App\Models\Form;
use App\Services\FormService;
use Livewire\Component;

class Versions extends Component
{
    public Form $form;

    public function rollback(int $versionId)
    {
        $version = $this->form->formVersions()->findOrFail($versionId);

        app(FormService::class)->rollback($this->form, $version);

        session()->flash('success', "Restored version {$version->version}.");

        return redirect()->route('forms.builder', $this->form);
    }

    public function countFields(?array $schema): int
    {
        $count = 0;

        foreach ($schema['steps'] ?? [] as $step) {
            foreach ($step['sections'] ?? [] as $section) {
                $count += count($section['fields'] ?? []);
            }
        }

        return $count;
    }

    public function render()
    {
        return view('livewire.forms.versions', [
            'versions' => $this->form->formVersions()->latest('created_at')->latest('id')->get(),
        ]);
    }
}
