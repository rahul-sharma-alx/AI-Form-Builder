<div>

    <div class="flex justify-between items-center mb-2">
        <h3 class="font-bold">JSON Schema</h3>
        <div class="flex gap-2">
            <button wire:click="reload" class="text-xs px-2 py-1 bg-gray-100 border rounded hover:bg-gray-200">
                Reload
            </button>
            <button wire:click="apply" class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                Apply JSON
            </button>
        </div>
    </div>

    <textarea
        wire:model.live.debounce.500ms="rawJson"
        spellcheck="false"
        class="w-full h-64 bg-gray-900 text-green-400 p-3 rounded font-mono text-xs overflow-auto"
    ></textarea>

    @if($error)
        <div class="mt-2 text-red-600 text-sm">
            {{ $error }}
        </div>
    @elseif($userEdited)
        <div class="mt-2 text-yellow-600 text-sm">
            Unsaved changes — click "Apply JSON" to update the schema.
        </div>
    @endif

</div>
