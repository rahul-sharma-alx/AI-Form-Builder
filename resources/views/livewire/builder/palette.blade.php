<div>

    <h2 class="font-bold mb-4">
        Fields
    </h2>

    @foreach($fieldTypes as $type)

        <button
            wire:click="add('{{ $type }}')"
            wire:key="palette-{{ $type }}"
            class="w-full mb-2 p-2 bg-gray-100 rounded text-left capitalize hover:bg-gray-200"
        >
            {{ $type }}
        </button>

    @endforeach

</div>
