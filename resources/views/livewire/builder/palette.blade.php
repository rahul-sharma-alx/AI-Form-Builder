<div>

    <h2 class="mb-4 font-bold">
        Fields
    </h2>

    <div class="grid grid-cols-2 gap-2">
        @foreach($fieldTypes as $type)
            <button
                wire:click="add('{{ $type }}')"
                wire:key="palette-{{ $type }}"
                class="btn btn-outline h-auto px-2 py-2 text-xs capitalize"
            >
                {{ $type }}
            </button>
        @endforeach
    </div>

</div>
