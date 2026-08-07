<div>

    <div class="max-w-3xl mx-auto py-10 px-4">

        <a href="{{ route('forms.builder', $form) }}" class="text-sm text-blue-600">&larr; Back to builder</a>

        <h1 class="text-3xl font-bold mt-1 mb-2">Generate Form with AI</h1>
        <p class="text-gray-500 mb-6">{{ $form->title }}</p>

        @if($this->job && in_array($this->job->status, ['pending', 'processing'], true))
            <div wire:poll.2s class="bg-white rounded-lg shadow p-8 text-center">
                <div class="text-lg font-semibold mb-2">Generating your form...</div>
                <p class="text-gray-500">The AI is building your schema. This page updates automatically.</p>
            </div>

        @elseif($this->job && $this->job->status === 'completed')
            <div wire:poll.2s class="bg-white rounded-lg shadow p-8 text-center">
                <div class="text-green-600 text-lg font-semibold mb-2">Form generated successfully!</div>
                <p class="text-gray-500 mb-4">Your schema has been saved to this form.</p>
                <a href="{{ route('forms.builder', $form) }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded">Open in Builder</a>
            </div>

        @elseif($this->job && $this->job->status === 'failed')
            <div class="bg-white rounded-lg shadow p-8">
                <div class="text-red-600 text-lg font-semibold mb-2">Generation failed</div>
                <p class="text-gray-500 mb-4">{{ $this->job->error_message }}</p>
                <button wire:click="start" class="bg-blue-600 text-white px-4 py-2 rounded">Try Again</button>
            </div>

        @else
            <form wire:submit="start" class="bg-white rounded-lg shadow p-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Describe the form you want</label>
                <textarea
                    wire:model="description"
                    rows="5"
                    placeholder="e.g. A customer feedback survey with satisfaction rating, contact details and an open comment box."
                    class="w-full border rounded px-3 py-2"></textarea>
                @error('description')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror

                <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded font-semibold">
                    Generate Form
                </button>
            </form>
        @endif

    </div>

</div>
