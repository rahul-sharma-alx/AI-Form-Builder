<div x-data="{ showKey: false }">

    <div class="max-w-2xl">

        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight">Settings</h1>
            <p class="text-sm text-muted-foreground">Configure the AI API used by the builder to generate and edit forms.</p>
        </div>

        @if($tested)
            <div class="mb-6 flex items-start gap-3 rounded-md border px-4 py-3 text-sm {{ $connected ? 'border-emerald-600/40 bg-emerald-50 text-emerald-800' : 'border-destructive/40 bg-destructive/10 text-destructive' }}" role="alert">
                @if($connected)
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mt-0.5 h-4 w-4 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mt-0.5 h-4 w-4 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                @endif
                <span>{{ $message }}</span>
            </div>
        @endif

        <form wire:submit="save" class="card p-6">
            <div class="mb-4">
                <label for="provider" class="label">AI Provider</label>
                <select id="provider" wire:model.live="provider" class="input">
                    @foreach($this->providerOptions() as $slug => $provider)
                        <option value="{{ $slug }}">{{ $provider['name'] }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-muted-foreground">Choose which API service the builder uses for AI generation and editing.</p>
                @error('provider')
                    <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="apiKey" class="label">API Key</label>
                <div class="relative">
                    <input
                        id="apiKey"
                        :type="showKey ? 'text' : 'password'"
                        wire:model="apiKey"
                        autocomplete="off"
                        placeholder="sk-..."
                        class="input pr-10"
                    >
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex w-10 cursor-pointer items-center justify-center text-muted-foreground transition-colors duration-fast hover:text-foreground"
                        @click="showKey = !showKey"
                        aria-label="Toggle API key visibility"
                    >
                        <svg x-show="!showKey" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        <svg x-show="showKey" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                    </button>
                </div>
                <p class="mt-1 text-xs text-muted-foreground">Leave blank to keep the currently saved key for this provider.</p>
                @error('apiKey')
                    <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="model" class="label">Model Name</label>
                <input id="model" type="text" wire:model="model" placeholder="e.g. gpt-4o-mini, gemini-1.5-flash, anthropic/claude-3.5-sonnet" class="input">
                @error('model')
                    <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary w-full sm:w-auto">
                    <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Save &amp; Test Connection
                </button>
                {{-- <p class="mt-2 text-xs text-muted-foreground">Values are written to your <code>.env</code> file. Your current OpenRouter key is kept if you leave the API key blank.</p> --}}
            </div>
        </form>

    </div>

</div>
