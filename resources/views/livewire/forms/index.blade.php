<div>

    <div class="flex justify-between mb-6">

        <h1 class="text-3xl font-bold">
            Forms
        </h1>

        <a
            href="{{ route('forms.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded"
        >
            New Form
        </a>

    </div>

    <div class="mb-4">
        <select wire:model.live="status" class="border rounded px-3 py-2">
            <option value="all">All</option>
            <option value="draft">Draft</option>
            <option value="published">Published</option>
        </select>
    </div>

    @if(session()->has('success'))
        <div class="mb-4 text-green-600">{{ session('success') }}</div>
    @endif

    <table class="w-full bg-white">

        <thead>
        <tr class="text-left">
            <th>Title</th>
            <th>Status</th>
            <th>Version</th>
            <th>Updated</th>
            <th></th>
        </tr>
        </thead>

        <tbody>

        @forelse($forms as $form)

            <tr class="border-t">

                <td>{{ $form->title }}</td>

                <td>{{ $form->status }}</td>

                <td>{{ $form->version }}</td>

                <td>{{ $form->updated_at?->diffForHumans() }}</td>

                <td class="flex gap-3">

                    <a href="{{ route('forms.builder',$form) }}">

                        Builder

                    </a>

                    <a href="{{ route('forms.public',$form) }}">

                        View

                    </a>

                    <a href="{{ route('forms.submissions',$form) }}">

                        Submissions

                    </a>

                    <a href="{{ route('forms.ai',$form) }}">

                        AI

                    </a>

                    <button
                        wire:click="delete({{ $form->id }})"
                        wire:confirm="Delete this form permanently?"
                        class="text-red-600"
                    >
                        Delete
                    </button>

                </td>

            </tr>

        @empty

            <tr><td colspan="5" class="text-center text-gray-500 py-6">No forms yet.</td></tr>

        @endforelse

        </tbody>

    </table>

    {{ $forms->links() }}

</div>