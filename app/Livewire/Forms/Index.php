<?php

namespace App\Livewire\Forms;

use Livewire\Component;
use App\Models\Form;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = 'all';
    public string $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function delete(Form $form)
    {
        $form->delete();
        session()->flash('success', 'Form deleted.');
    }

    public function render()
    {
        $forms = Form::query()
            ->when($this->search !== '', fn ($q) => $q->where('title', 'like', "%{$this->search}%"));

        if ($this->status === 'draft') {
            $forms->draft();
        } elseif ($this->status === 'published') {
            $forms->published();
        }

        return view('livewire.forms.index', [
            'forms' => $forms->latest()->paginate(10),
        ]);
    }
}