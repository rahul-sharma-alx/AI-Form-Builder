<?php

namespace App\Livewire\Ai;

use App\Models\AiJob;
use App\Models\Form;
use App\Services\AiService;
use App\Services\FormService;
use Livewire\Component;

class Edit extends Component
{
    public Form $form;

    public string $instruction = '';

    public ?int $jobId = null;

    public function start()
    {
        $this->validate([
            'instruction' => 'required|string|max:2000',
        ]);

        $this->jobId = app(AiService::class)->dispatchEdit($this->form, $this->instruction)->id;
    }

    public function getJobProperty(): ?AiJob
    {
        return $this->jobId ? AiJob::find($this->jobId) : null;
    }

    public function apply()
    {
        $job = $this->job;

        if (! $job || $job->status !== 'completed' || empty($job->diff['schema'])) {
            return;
        }

        app(FormService::class)->autosave($this->form, ['schema' => $job->diff['schema']]);

        session()->flash('success', 'AI edits applied to the form.');
    }

    public function render()
    {
        return view('livewire.ai.edit');
    }
}
