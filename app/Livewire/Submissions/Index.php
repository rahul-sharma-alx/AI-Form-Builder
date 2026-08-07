<?php

namespace App\Livewire\Submissions;

use App\Models\Form;
use App\Services\SubmissionService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public Form $form;

    public string $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function export()
    {
        return app(SubmissionService::class)->exportCsv($this->form, $this->search);
    }

    public function render()
    {
        return view('livewire.submissions.index', [
            'submissions' => app(SubmissionService::class)->query($this->form, $this->search)->latest()->paginate(15),
            'fields' => app(SubmissionService::class)->columns($this->form),
        ]);
    }
}
