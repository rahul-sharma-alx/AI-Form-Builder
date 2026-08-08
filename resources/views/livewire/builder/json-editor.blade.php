<div>

    <div class="flex justify-between items-center mb-2">
        <h3 class="font-bold">JSON Schema</h3>
        <div class="flex gap-2">
            <button wire:click="reload" class="btn btn-secondary btn-sm">Reload</button>
            <button wire:click="apply" class="btn btn-primary btn-sm">Apply JSON</button>
        </div>
    </div>

    <textarea
        wire:model.live.debounce.500ms="rawJson"
        spellcheck="false"
        class="h-64 w-full rounded-md border border-border bg-card p-3 font-mono text-xs text-foreground"
    ></textarea>

    @if($error)
        <div class="mt-2 text-sm text-destructive">
            {{ $error }}
        </div>
    @elseif($userEdited)
        <div class="mt-2 text-sm text-amber-600">
            Unsaved changes — click "Apply JSON" to update the schema.
        </div>
    @endif

</div>
