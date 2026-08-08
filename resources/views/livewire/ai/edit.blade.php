<div>

    <div class="max-w-3xl mx-auto">

        <a href="{{ route('forms.builder', $form) }}" class="btn-link text-sm">&larr; Back to builder</a>

        <h1 class="mt-2 text-2xl font-bold tracking-tight mb-2">Edit Form with AI</h1>
        <p class="text-sm text-muted-foreground mb-6">{{ $form->title }}</p>

        @if(session()->has('success'))
            <div class="mb-4 rounded-md border border-border bg-card px-4 py-3 text-sm text-emerald-600" role="alert">{{ session('success') }}</div>
        @endif

        @if($this->job && in_array($this->job->status, ['pending', 'processing'], true))
            <div wire:poll.2s class="card p-8 text-center">
                <div class="mb-2 text-lg font-semibold">Applying your edits...</div>
                <p class="text-sm text-muted-foreground">The AI is modifying your schema. This page updates automatically.</p>
            </div>

        @elseif($this->job && $this->job->status === 'completed')
            <div wire:poll.2s class="card p-8">

                <div class="mb-1 text-lg font-semibold text-emerald-600">Edits ready for review</div>
                <p class="mb-4 text-sm text-muted-foreground">{{ $this->job->diff['summary'] ?? 'No changes detected' }}</p>

                @if(!empty($this->job->diff['changes']))
                    <ul class="mb-6 space-y-2 divide-y divide-border">
                        @foreach($this->job->diff['changes'] as $change)
                            <li class="py-2 text-sm">
                                @php
                                    $color = $change['change'] === 'added' ? 'text-emerald-700'
                                        : ($change['change'] === 'removed' ? 'text-destructive' : 'text-amber-700');
                                @endphp

                                <span class="text-xs font-semibold uppercase tracking-wide {{ $color }}">
                                    {{ $change['change'] }} {{ $change['entity'] }}
                                </span>
                                <span class="text-foreground">— {{ $change['label'] }}</span>

                                @if(!empty($change['fields']))
                                    <ul class="mt-1 space-y-0.5 pl-4 text-xs text-muted-foreground">
                                        @foreach($change['fields'] as $prop => $diff)
                                            <li>
                                                {{ $prop }}:
                                                <span class="line-through">{{ is_scalar($diff['from']) ? $diff['from'] : json_encode($diff['from']) }}</span>
                                                →
                                                <span class="text-foreground">{{ is_scalar($diff['to']) ? $diff['to'] : json_encode($diff['to']) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="flex gap-3">
                    <button wire:click="apply" class="btn btn-primary">Apply Changes</button>
                    <a href="{{ route('forms.builder', $form) }}" class="btn btn-outline">Open in Builder</a>
                </div>
            </div>

        @elseif($this->job && $this->job->status === 'failed')
            <div class="card p-8">
                <div class="mb-2 text-lg font-semibold text-destructive">Edit failed</div>
                <p class="mb-4 text-sm text-muted-foreground">{{ $this->job->error_message }}</p>
                <button wire:click="start" class="btn btn-primary">Try Again</button>
            </div>

        @else
            <form wire:submit="start" class="card p-6">
                <label class="label">What should change?</label>
                <textarea
                    wire:model="instruction"
                    rows="4"
                    placeholder="e.g. Translate all labels to Spanish, remove the phone field, make email required, add a new section called Details with a rating question."
                    class="input py-2"></textarea>
                @error('instruction')
                    <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                @enderror

                <p class="mt-2 text-xs text-muted-foreground">The AI edits your existing schema in place — it never regenerates the whole form. You review the diff before applying.</p>

                <button type="submit" class="btn btn-primary mt-4">Preview Edits</button>
            </form>
        @endif

    </div>

</div>