<?php

namespace App\Livewire\Ai;

use App\Models\AiJob;
use App\Models\Form;
use App\Services\AiService;
use Livewire\Component;

class Generate extends Component
{
    public Form $form;

    public string $description = '';

    public ?int $jobId = null;

    public function start()
    {
        $this->validate([
            'description' => 'required|string|max:2000',
        ]);

        $this->jobId = app(AiService::class)->dispatchGeneration($this->form, $this->description)->id;
    }

    public function getJobProperty(): ?AiJob
    {
        return $this->jobId ? AiJob::find($this->jobId) : null;
    }

    public function render()
    {
        return view('livewire.ai.generate');
    }
}
