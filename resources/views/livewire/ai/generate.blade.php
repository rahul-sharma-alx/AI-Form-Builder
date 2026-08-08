<div>

    <div class="max-w-3xl mx-auto">

        <a href="{{ route('forms.builder', $form) }}" class="btn-link text-sm">&larr; Back to builder</a>

        <h1 class="mt-2 text-2xl font-bold tracking-tight mb-2">Generate Form with AI</h1>
        <p class="text-sm text-muted-foreground mb-6">{{ $form->title }}</p>

        @if($this->job && in_array($this->job->status, ['pending', 'processing'], true))
            <div wire:poll.2s class="card p-8 text-center">
                <div class="mb-2 text-lg font-semibold">Generating your form...</div>
                <p class="text-sm text-muted-foreground">The AI is building your schema. This page updates automatically.</p>
            </div>

        @elseif($this->job && $this->job->status === 'completed')
            <div wire:poll.2s class="card p-8 text-center">
                <div class="mb-2 text-lg font-semibold text-emerald-600">Form generated successfully!</div>
                <p class="mb-4 text-sm text-muted-foreground">Your schema has been saved to this form.</p>
                <a href="{{ route('forms.builder', $form) }}" class="btn btn-primary">Open in Builder</a>
            </div>

        @elseif($this->job && $this->job->status === 'failed')
            <div class="card p-8">
                <div class="mb-2 text-lg font-semibold text-destructive">Generation failed</div>
                <p class="mb-4 text-sm text-muted-foreground">{{ $this->job->error_message }}</p>
                <button wire:click="start" class="btn btn-primary">Try Again</button>
            </div>

        @else
            <form wire:submit="start" class="card p-6">
                <label class="label">Describe the form you want</label>
                <textarea
                    wire:model="description"
                    rows="5"
                    placeholder="e.g. A customer feedback survey with satisfaction rating, contact details and an open comment box."
                    class="input py-2"></textarea>
                @error('description')
                    <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                @enderror

                <button type="submit" class="btn btn-primary mt-4">Generate Form</button>
            </form>
        @endif

    </div>

</div>