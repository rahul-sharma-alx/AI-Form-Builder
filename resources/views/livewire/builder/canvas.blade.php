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
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-fast
                            {{ $currentStepId === $step['id']
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted text-muted-foreground hover:bg-accent hover:text-accent-foreground' }}">
                        {{ $step['title'] }}
                    </button>
                </div>
            @endforeach
        </div>
        <button wire:click="addStep" class="btn btn-ghost btn-sm">+ Step</button>
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
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-fast
                                {{ $currentSectionId === $section['id']
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-muted text-muted-foreground hover:bg-accent hover:text-accent-foreground' }}">
                            {{ $section['title'] }}
                        </button>
                    </div>
                @endforeach
            </div>
            <button wire:click="addSection" class="btn btn-ghost btn-sm">+ Section</button>
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
                    class="cursor-pointer rounded-md border p-3 transition-colors duration-fast
                        {{ $selectedFieldId === $field['id']
                            ? 'border-primary bg-accent/50 ring-1 ring-primary/40'
                            : 'border-border hover:border-primary/50' }}">

                    <strong>{{ $field['label'] ?? $field['key'] ?? 'Untitled' }}</strong>

                    <div class="text-sm text-muted-foreground capitalize">
                        {{ $field['type'] ?? 'unknown' }}
                    </div>

                    @if(($field['type'] ?? '') === 'html')
                        <div class="mt-1 truncate text-xs text-muted-foreground">{!! strip_tags((string) ($field['content'] ?? '')) ?: 'HTML block — no content yet' !!}</div>
                    @endif

                    @if(!empty($field['placeholder']))
                        <div class="text-xs text-muted-foreground mt-1">
                            Placeholder:
                            {{ $field['placeholder'] }}
                        </div>
                    @endif

                    @if($field['required'])
                        <span class="mt-2 inline-block rounded bg-destructive/10 px-2 py-1 text-xs font-medium text-destructive">
                            Required
                        </span>
                    @endif

                    <div class="mt-3 flex gap-2">
                        <button
                            wire:click.stop="duplicate('{{ $field['id'] }}')"
                            class="btn btn-secondary btn-sm">
                            Duplicate
                        </button>
                        <button
                            wire:click.stop="delete('{{ $field['id'] }}')"
                            class="btn btn-danger btn-sm">
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-muted-foreground">No fields yet. Click a field type on the left to add.</p>
            @endforelse
        </div>

    @else
        <p class="text-muted-foreground">No steps found. Add a step to get started.</p>
    @endif

    @script
        <script>
            initSortable();
        </script>
    @endscript

</div>
