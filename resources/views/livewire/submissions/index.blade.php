<div>

    <div class="flex justify-between items-start mb-6">

        <div>
            <a href="{{ route('forms.builder', $form) }}" class="text-sm text-blue-600">&larr; Back to builder</a>
            <h1 class="text-3xl font-bold mt-1">Submissions</h1>
            <p class="text-gray-500">{{ $form->title }}</p>
        </div>

        <button
            wire:click="export"
            class="bg-green-600 text-white px-4 py-2 rounded"
        >
            Export CSV
        </button>

    </div>

    <div class="mb-4">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Search submissions..."
            class="border rounded px-3 py-2 w-64"
        >
    </div>

    @if(session()->has('success'))
        <div class="mb-4 text-green-600">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto bg-white">
        <table class="w-full">
            <thead>
            <tr class="text-left">
                <th class="px-3 py-2">Submitted</th>
                <th class="px-3 py-2">IP Address</th>
                @foreach($fields as $field)
                    <th class="px-3 py-2">{{ $field['label'] ?? $field['key'] }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @forelse($submissions as $submission)
                <tr class="border-t">
                    <td class="px-3 py-2 whitespace-nowrap">{{ $submission->created_at?->diffForHumans() }}</td>
                    <td class="px-3 py-2">{{ $submission->ip_address }}</td>
                    @foreach($fields as $key => $field)
                        @php
                            $value = $submission->data[$key] ?? '';
                            $display = is_array($value) ? implode('; ', $value) : $value;
                        @endphp
                        <td class="px-3 py-2">{{ $display }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($fields) + 2 }}" class="text-center text-gray-500 py-6">No submissions yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $submissions->links() }}
    </div>

</div>
