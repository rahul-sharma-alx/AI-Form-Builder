<div>

    <h2 class="font-bold mb-4">
        Field Properties
    </h2>

    @if(empty($field))

        <p class="text-gray-400">
            Select a field to edit its properties.
        </p>

    @else

        @php $type = $field['type'] ?? ''; @endphp

        {{-- Label (all types) --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Label</label>
            <input type="text" wire:model.live="field.label" class="w-full border p-2 rounded">
        </div>

        {{-- Help Text (all types) --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Help Text</label>
            <input type="text" wire:model.live="field.help" class="w-full border p-2 rounded">
        </div>

        {{-- Required (all types except heading/section) --}}
        @if(!in_array($type, ['heading', 'section']))
            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" wire:model.live="field.required" class="mr-2">
                    Required
                </label>
            </div>
        @endif

        {{-- Default (all input types) --}}
        @if(!in_array($type, ['heading', 'section', 'file']))
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Default Value</label>
                <input type="text" wire:model.live="field.default" class="w-full border p-2 rounded">
            </div>
        @endif

        {{-- Placeholder (text-like) --}}
        @if(in_array($type, ['text', 'textarea', 'email', 'phone', 'number']))
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Placeholder</label>
                <input type="text" wire:model.live="field.placeholder" class="w-full border p-2 rounded">
            </div>
        @endif

        {{-- Min (number, date, rating) --}}
        @if(in_array($type, ['number', 'date', 'rating']))
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">
                    @if($type === 'rating') Max Stars
                    @else Min @endif
                </label>
                <input type="number" wire:model.live="field.min" class="w-full border p-2 rounded">
            </div>
        @endif

        {{-- Max (number, date, rating) --}}
        @if(in_array($type, ['number', 'date']))
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Max</label>
                <input type="number" wire:model.live="field.max" class="w-full border p-2 rounded">
            </div>
        @endif

        {{-- Regex (text-like) --}}
        @if(in_array($type, ['text', 'textarea', 'email', 'phone']))
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Regex Pattern</label>
                <input type="text" wire:model.live="field.regex" placeholder="/^[a-z]+$/i" class="w-full border p-2 rounded font-mono text-sm">
            </div>
        @endif

        {{-- Options (dropdown, radio, checkbox) --}}
        @if(in_array($type, ['dropdown', 'radio', 'checkbox']))
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Options</label>

                @forelse($field['options'] ?? [] as $i => $opt)
                    <div wire:key="opt-{{ $i }}" class="flex gap-2 mb-2">
                        <input type="text" wire:model.live="field.options.{{ $i }}.label" placeholder="Label" class="border p-1 w-full rounded">
                        <input type="text" wire:model.live="field.options.{{ $i }}.value" placeholder="Value" class="border p-1 w-full rounded">
                        <button wire:click="removeOption({{ $i }})" class="px-2 text-red-500">×</button>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 mb-2">No options yet.</p>
                @endforelse

                <button wire:click="addOption" class="text-sm text-blue-600">+ Add Option</button>
            </div>
        @endif

        {{-- Validation (all input types) --}}
        @if(!in_array($type, ['heading', 'section']))
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Validation Rules</label>
                <input type="text" wire:model.live="field.validation" placeholder="min:5|max:100" class="w-full border p-2 rounded font-mono text-sm">
                <p class="text-xs text-gray-400 mt-1">Laravel validation rule string.</p>
            </div>
        @endif

    @endif

</div>
