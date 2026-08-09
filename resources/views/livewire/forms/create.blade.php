<div>

    <div class="max-w-3xl">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight">Create Form</h1>
            <p class="text-sm text-muted-foreground">Start from a template or begin with a blank form.</p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <button
                type="button"
                wire:click="$set('templateId', '')"
                class="card cursor-pointer p-4 text-left transition-colors duration-fast {{ $templateId === '' ? 'border-primary ring-2 ring-primary/30' : 'hover:border-primary/50' }}"
            >
                <div class="font-semibold">Blank form</div>
                <div class="text-sm text-muted-foreground">Start with a single empty section.</div>
            </button>

            @foreach($this->templates() as $template)
                <button
                    type="button"
                    wire:click="startFromTemplate('{{ $template['id'] }}')"
                    class="card group cursor-pointer p-4 text-left transition-colors duration-fast hover:border-primary/50 hover:ring-2 hover:ring-primary/30"
                >
                    <div class="flex items-center justify-between gap-2">
                        <div class="font-semibold">{{ $template['name'] }}</div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-fast group-hover:translate-x-0.5 group-hover:text-primary"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </div>
                    <div class="text-sm text-muted-foreground">{{ $template['description'] }}</div>
                </button>
            @endforeach
        </div>

        <form wire:submit="save" class="card mt-8 p-6">
            <h2 class="mb-4 text-lg font-semibold">Form details</h2>

            <div class="mb-4">
                <label for="title" class="label">Form Title</label>
                <input id="title" type="text" wire:model="title" placeholder="e.g. Customer Feedback" class="input">
                @error('title')
                    <div class="mt-1 text-sm text-destructive">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="label">Description</label>
                <textarea id="description" wire:model="description" rows="3" class="input py-2" placeholder="What is this form for?"></textarea>
            </div>

            <button class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75h1.5m9 0h-9"/></svg>
                Save Draft
            </button>
        </form>
    </div>

</div>
