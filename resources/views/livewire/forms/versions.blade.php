<div>

    <div class="max-w-4xl mx-auto px-4">

        <a href="{{ route('forms.builder', $form) }}" class="btn-link text-sm">&larr; Back to builder</a>

        <h1 class="mt-2 text-2xl font-bold tracking-tight">Version History</h1>
        <p class="text-sm text-muted-foreground mb-6">
            {{ $form->title }} &middot; a snapshot is recorded every time the schema changes. Keep the latest 25.
        </p>

        @if(session()->has('success'))
            <div class="mb-4 rounded-md border border-border bg-card px-4 py-3 text-sm text-emerald-600" role="alert">{{ session('success') }}</div>
        @endif

        <div class="panel overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-border bg-muted/40 text-left">
                    <th class="table-hd px-4 py-3">Version</th>
                    <th class="table-hd px-4 py-3">Saved</th>
                    <th class="table-hd px-4 py-3">Fields</th>
                    <th class="table-hd px-4 py-3 text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($versions as $version)
                    <tr class="border-b border-border last:border-0 transition-colors duration-fast hover:bg-accent/40">
                        <td class="px-4 py-3 font-semibold">v{{ $version->version }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $version->created_at?->format('M j, Y g:i A') }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $this->countFields($version->schema) }} fields</td>
                        <td class="px-4 py-3 text-right">
                            <button
                                wire:click="rollback({{ $version->id }})"
                                wire:confirm="Restore this version? Current schema will be overwritten."
                                class="btn btn-ghost btn-sm text-primary"
                            >
                                Restore
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-muted-foreground">No versions recorded yet. Save the form once to create the first snapshot.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
