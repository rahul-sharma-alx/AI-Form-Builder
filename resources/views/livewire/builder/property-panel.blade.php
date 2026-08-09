<div>

    <h2 class="mb-4 font-bold">
        Field Properties
    </h2>

    @if(empty($field))

        <p class="text-muted-foreground text-sm">
            Select a field to edit its properties.
        </p>

    @else

        @php $type = $field['type'] ?? ''; @endphp

        {{-- Label (all types) --}}
        <div class="mb-4">
            <label class="label">Label</label>
            <input type="text" wire:model.live="field.label" class="input">
        </div>

        {{-- Help Text (all types) --}}
        <div class="mb-4">
            <label class="label">Help Text</label>
            <input type="text" wire:model.live="field.help" class="input">
        </div>

        {{-- HTML Content (html type) --}}
        @if($type === 'html')
            <div class="mb-4">
                <label class="label">HTML Content</label>
                <textarea
                    wire:model.live="field.content"
                    rows="6"
                    class="input py-2 font-mono text-xs"
                    placeholder="<p>Some text</p><div>Any HTML works</div>"
                ></textarea>
                <p class="mt-1 text-xs text-muted-foreground">Rendered as-is on the public form — any tags like &lt;p&gt;, &lt;div&gt;, &lt;a&gt;, &lt;img&gt; work.</p>
            </div>
        @endif

        {{-- Required (all types except heading/section/html) --}}
        @if(!in_array($type, ['heading', 'section', 'html']))
            <div class="mb-4">
                <label class="inline-flex items-center gap-2 text-sm font-medium">
                    <input type="checkbox" wire:model.live="field.required" class="h-4 w-4 rounded border-input accent-primary">
                    Required
                </label>
            </div>
        @endif

        {{-- Default (all input types) --}}
        @if(!in_array($type, ['heading', 'section', 'html', 'file']))
            <div class="mb-4">
                <label class="label">Default Value</label>
                <input type="text" wire:model.live="field.default" class="input">
            </div>
        @endif

        {{-- Placeholder (text-like) --}}
        @if(in_array($type, ['text', 'textarea', 'email', 'phone', 'number']))
            <div class="mb-4">
                <label class="label">Placeholder</label>
                <input type="text" wire:model.live="field.placeholder" class="input">
            </div>
        @endif

        {{-- Min (number, date) --}}
        @if(in_array($type, ['number', 'date']))
            <div class="mb-4">
                <label class="label">Min</label>
                <input type="number" wire:model.live="field.min" class="input">
            </div>
        @endif

        {{-- Max (number, date, rating) --}}
        @if(in_array($type, ['number', 'date', 'rating']))
            <div class="mb-4">
                <label class="label">
                    @if($type === 'rating') Max Stars
                    @else Max @endif
                </label>
                <input type="number" wire:model.live="field.max" class="input">
            </div>
        @endif

        {{-- Regex (text-like) --}}
        @if(in_array($type, ['text', 'textarea', 'email', 'phone']))
            <div class="mb-4">
                <label class="label">Regex Pattern</label>
                <input type="text" wire:model.live="field.regex" placeholder="/^[a-z]+$/i" class="input font-mono text-sm">
            </div>
        @endif

        {{-- Options (dropdown, radio, checkbox) --}}
        @if(in_array($type, ['dropdown', 'radio', 'checkbox']))
            <div class="mb-4">
                <label class="label">Options</label>

                @forelse($field['options'] ?? [] as $i => $opt)
                    <div wire:key="opt-{{ $i }}" class="mb-2 flex gap-2">
                        <input type="text" wire:model.live="field.options.{{ $i }}.label" placeholder="Label" class="input flex-1">
                        <input type="text" wire:model.live="field.options.{{ $i }}.value" placeholder="Value" class="input flex-1">
                        <button wire:click="removeOption({{ $i }})" class="btn btn-ghost btn-icon text-destructive">×</button>
                    </div>
                @empty
                    <p class="mb-2 text-xs text-muted-foreground">No options yet.</p>
                @endforelse

                <button wire:click="addOption" class="btn-link text-sm">+ Add Option</button>
            </div>
        @endif

        {{-- Validation (all input types) --}}
        @if(!in_array($type, ['heading', 'section', 'html']))
            <div class="mb-4">
                <label class="label">Validation Rules</label>
                <input type="text" wire:model.live="field.validation" placeholder="min:5|max:100" class="input font-mono text-sm">
                <p class="mt-1 text-xs text-muted-foreground">Laravel validation rule string.</p>
                @if($validationError)
                    <p class="mt-1 text-xs text-destructive">Invalid rule: {{ $validationError }}</p>
        {{-- Conditional visibility (answerable types) --}}
        @if(!in_array($type, ['heading', 'section', 'html', 'file']))
            <div class="mb-4">
                <label class="inline-flex items-center gap-2 text-sm font-medium">
                    <input type="checkbox" wire:model="visibilityEnabled" class="h-4 w-4 rounded border-input accent-primary">
                    Show conditionally
                </label>
                <p class="mt-1 text-xs text-muted-foreground">Only show this field when another field matches a rule.</p>

                @if(!empty($field['visibility']))
                    <div class="mt-3 space-y-2 text-sm">
                        <select wire:model.live="field.visibility.field" class="input">
                            @foreach($candidateFields as $candidate)
                                <option value="{{ $candidate['key'] }}">{{ $candidate['label'] }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="field.visibility.op" class="input">
                            <option value="equals">is equal to</option>
                            <option value="not_equals">is not equal to</option>
                            <option value="empty">is empty</option>
                            <option value="not_empty">is not empty</option>
                        </select>

                        @if(in_array($field['visibility']['op'] ?? '', ['equals', 'not_equals']))
                            <input
                                type="text"
                                wire:model.live="field.visibility.value"
                                placeholder="Value"
                                class="input"
                            >
                        @endif
                    </div>
                @endif
            </div>
        @endif

    @endif
            </div>
        @endif

    @endif

</div>
