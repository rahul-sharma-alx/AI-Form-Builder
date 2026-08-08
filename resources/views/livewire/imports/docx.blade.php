<div>

    <div class="max-w-3xl mx-auto">

        <a href="{{ route('forms.index') }}" class="btn-link text-sm">&larr; Back to forms</a>

        <h1 class="mt-2 text-2xl font-bold tracking-tight mb-2">Import from Word (.docx)</h1>
        <p class="text-sm text-muted-foreground mb-6">Upload a Word document — headings, questions, checkboxes and options are extracted automatically. You review the mapping before a form is created.</p>

        @if(! $this->import)

            <form wire:submit="upload" class="card p-6">
                <label class="label">Word document</label>
                <input type="file" wire:model="file" accept=".docx" class="input">
                @error('file')
                    <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                @enderror

                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary mt-4">Upload &amp; Parse</button>
            </form>

        @elseif(in_array($this->import->status, ['pending', 'processing'], true))

            <div wire:poll.2s class="card p-8 text-center">
                <div class="mb-2 text-lg font-semibold">Parsing {{ $this->import->file_name }}...</div>
                <p class="text-sm text-muted-foreground">The document is being processed in the background. This page updates automatically.</p>
            </div>

        @elseif($this->import->status === 'failed')

            <div class="card p-8">
                <div class="mb-2 text-lg font-semibold text-destructive">Import failed</div>
                <p class="mb-4 text-sm text-muted-foreground">{{ $this->import->error_message }}</p>
                <button wire:click="$set('importId', null)" class="btn btn-primary">Try Another File</button>
            </div>

        @elseif($this->import->status === 'completed')

            <div class="card mb-6 p-6">
                <label class="label">Form title</label>
                <input wire:model="title" class="input">
                @error('title')
                    <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <form wire:submit="createForm" class="card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Review the mapping</h2>
                    <span class="text-xs text-muted-foreground">{{ count($items) }} items</span>
                </div>

                @forelse($items as $index => $item)
                    <div wire:key="item-{{ $item['id'] }}" class="border-t border-border py-4">

                        @if($item['type'] === 'heading')
                            <div class="flex items-center gap-3">
                                <span class="rounded bg-purple-100 px-2 py-0.5 text-xs font-semibold uppercase text-purple-700">Section</span>
                                <input
                                    wire:model="items.{{ $index }}.label"
                                    class="input flex-1 font-semibold"
                                    placeholder="Section title">
                                <button type="button" wire:click="removeItem({{ $index }})" class="btn-link text-sm text-destructive">Remove</button>
                            </div>
                        @else
                            <div class="flex items-start gap-3">
                                <span class="mt-1.5 rounded bg-blue-100 px-2 py-0.5 text-xs font-semibold uppercase text-blue-700">Question</span>
                                <div class="flex-1 space-y-2">
                                    <input
                                        wire:model="items.{{ $index }}.label"
                                        class="input"
                                        placeholder="Question label">
                                    <div class="flex gap-3">
                                        <select wire:model="items.{{ $index }}.field_type" class="input w-auto">
                                            @foreach($this->fieldTypes as $type)
                                                <option value="{{ $type }}">{{ $type }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" wire:click="removeItem({{ $index }})" class="btn-link mt-1.5 text-sm text-destructive">Remove</button>
                                    </div>

                                    @if(in_array($item['field_type'], ['dropdown', 'radio', 'checkbox'], true))
                                        <div class="space-y-1 pl-1">
                                            @foreach($item['options'] as $optionIndex => $option)
                                                <div class="flex items-center gap-2" wire:key="option-{{ $item['id'] }}-{{ $optionIndex }}">
                                                    <input
                                                        wire:model="items.{{ $index }}.options.{{ $optionIndex }}"
                                                        class="input flex-1 text-muted-foreground"
                                                        placeholder="Option">
                                                    <button type="button" wire:click="removeOption({{ $index }}, {{ $optionIndex }})" class="btn-link text-xs text-destructive">×</button>
                                                </div>
                                            @endforeach
                                            <button type="button" wire:click="addOption({{ $index }})" class="btn-link text-sm">+ Add option</button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>
                @empty
                    <p class="py-4 text-muted-foreground">No questions were detected in this document.</p>
                @endforelse

                <div class="mt-4">
                    <button type="button" wire:click="addQuestion" class="btn-link text-sm">+ Add question</button>
                </div>

                <button type="submit" class="btn btn-primary mt-6">Create Form</button>
            </form>

        @endif

    </div>

</div>
