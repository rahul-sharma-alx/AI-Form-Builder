<div>

    {{-- Step tabs --}}
    <div class="flex flex-wrap gap-2 mb-4 items-center">
        <div data-sortable="steps" class="flex flex-wrap gap-2">
            @foreach($schema['steps'] as $step)
                <div
                    data-id="{{ $step['id'] }}"
                    wire:key="step-{{ $step['id'] }}"
                    class="cursor-move"
                >
                    <button
                        wire:click="selectStep('{{ $step['id'] }}')"
                        class="px-3 py-1 rounded text-sm font-medium
                            {{ $currentStepId === $step['id']
                                ? 'bg-blue-600 text-white'
                                : 'bg-gray-200 hover:bg-gray-300' }}">
                        {{ $step['title'] }}
                    </button>
                </div>
            @endforeach
        </div>
        <button wire:click="addStep" class="px-3 py-1 bg-gray-100 border rounded text-sm">
            + Step
        </button>
    </div>

    {{-- Find current step --}}
    @php
        $currentStep = null;
        foreach ($schema['steps'] as $step) {
            if ($step['id'] === $currentStepId) {
                $currentStep = $step;
                break;
            }
        }
    @endphp

    @if($currentStep)

        {{-- Section tabs --}}
        <div class="flex flex-wrap gap-2 mb-4 items-center">
            <div data-sortable="sections" class="flex flex-wrap gap-2">
                @foreach($currentStep['sections'] as $section)
                    <div
                        data-id="{{ $section['id'] }}"
                        wire:key="section-{{ $section['id'] }}"
                        class="cursor-move"
                    >
                        <button
                            wire:click="selectSection('{{ $section['id'] }}')"
                            class="px-3 py-1 rounded text-sm
                                {{ $currentSectionId === $section['id']
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-200 hover:bg-gray-300' }}">
                            {{ $section['title'] }}
                        </button>
                    </div>
                @endforeach
            </div>
            <button wire:click="addSection" class="px-3 py-1 bg-gray-100 border rounded text-sm">
                + Section
            </button>
        </div>

        {{-- Find current section fields --}}
        @php
            $currentSection = null;
            if ($currentStep) {
                foreach ($currentStep['sections'] as $section) {
                    if ($section['id'] === $currentSectionId) {
                        $currentSection = $section;
                        break;
                    }
                }
            }
            $fields = $currentSection['fields'] ?? [];
        @endphp

        {{-- Fields of current section --}}
        <div data-sortable="fields" class="space-y-3">
            @forelse($fields as $field)
                <div
                    data-id="{{ $field['id'] }}"
                    wire:key="field-{{ $field['id'] }}"
                    wire:click="select('{{ $field['id'] }}')"
                    class="border rounded p-3 mb-3 cursor-pointer
                        {{ $selectedFieldId === $field['id']
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-gray-300' }}">

                    <strong>{{ $field['label'] ?? $field['key'] ?? 'Untitled' }}</strong>

                    <div class="text-gray-500">
                        {{ $field['type'] ?? 'unknown' }}
                    </div>

                    @if(!empty($field['placeholder']))
                        <div class="text-xs text-gray-400 mt-1">
                            Placeholder:
                            {{ $field['placeholder'] }}
                        </div>
                    @endif

                    @if($field['required'])
                        <span class="inline-block mt-2 px-2 py-1 bg-red-100 text-red-600 rounded text-xs">
                            Required
                        </span>
                    @endif

                    <div class="mt-3 flex gap-2">
                        <button
                            wire:click.stop="duplicate('{{ $field['id'] }}')"
                            class="px-2 py-1 bg-blue-500 text-white rounded text-sm">
                            Duplicate
                        </button>
                        <button
                            wire:click.stop="delete('{{ $field['id'] }}')"
                            class="px-2 py-1 bg-red-500 text-white rounded text-sm">
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-gray-400">No fields yet. Click a field type on the left to add.</p>
            @endforelse
        </div>

    @else
        <p class="text-gray-400">No steps found. Add a step to get started.</p>
    @endif

    @script
        <script>
            initSortable();
        </script>
    @endscript

</div>
