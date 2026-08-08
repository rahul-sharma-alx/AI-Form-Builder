<div>

    <div class="flex flex-wrap justify-between items-start gap-3 mb-6">

        <div>
            <a href="{{ route('forms.builder', $form) }}" class="btn-link text-sm">&larr; Back to builder</a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight">Submissions</h1>
            <p class="text-sm text-muted-foreground">{{ $form->title }}</p>
        </div>

        <button wire:click="export" class="btn btn-primary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Export CSV
        </button>

    </div>

    <div class="mb-4">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Search submissions..."
            class="input w-64"
        >
    </div>

    @if(session()->has('success'))
        <div class="mb-4 rounded-md border border-border bg-card px-4 py-3 text-sm text-emerald-600" role="alert">{{ session('success') }}</div>
    @endif

    <div class="panel overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
            <tr class="border-b border-border bg-muted/40">
                <th class="table-hd px-3 py-3">Submitted</th>
                <th class="table-hd px-3 py-3">IP Address</th>
                @foreach($fields as $field)
                    <th class="table-hd px-3 py-3">{{ $field['label'] ?? $field['key'] }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @forelse($submissions as $submission)
                <tr class="border-b border-border last:border-0 transition-colors duration-fast hover:bg-accent/40">
                    <td class="px-3 py-2 whitespace-nowrap">{{ $submission->created_at?->diffForHumans() }}</td>
                    <td class="px-3 py-2 text-muted-foreground">{{ $submission->ip_address }}</td>
                    @foreach($fields as $key => $field)
                        @php
                            $value = $submission->data[$key] ?? '';
                            $display = is_array($value) ? implode('; ', $value) : $value;
                        @endphp
                        <td class="px-3 py-2">{{ $display }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($fields) + 2 }}" class="px-3 py-6 text-center text-muted-foreground">No submissions yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $submissions->links() }}
    </div>

</div>