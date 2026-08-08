<div x-data="shareModal()">

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Forms</h1>
            <p class="text-sm text-muted-foreground">Build, publish and track your forms.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('imports.docx') }}" class="btn btn-outline btn-sm">Import DOCX</a>
            <a href="{{ route('imports.xlsx') }}" class="btn btn-outline btn-sm">Import XLSX</a>
            <a href="{{ route('forms.create') }}" class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Form
            </a>
        </div>
    </div>

    <div class="mb-4 flex items-center gap-2">
        <label for="status-filter" class="text-sm font-medium text-muted-foreground">Filter</label>
        <select id="status-filter" wire:model.live="status" class="input w-auto">
            <option value="all">All</option>
            <option value="draft">Draft</option>
            <option value="published">Published</option>
        </select>
    </div>

    @if(session()->has('success'))
        <div class="mb-4 rounded-md border border-border bg-card px-4 py-3 text-sm text-emerald-600" role="alert">
            {{ session('success') }}</div>
    @endif

    <div class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/40">
                        <th class="table-hd px-4 py-3">Title</th>
                        <th class="table-hd px-4 py-3">Status</th>
                        <th class="table-hd px-4 py-3">Version</th>
                        <th class="table-hd px-4 py-3">Updated</th>
                        <th class="table-hd px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $form)
                        <tr class="border-b border-border last:border-0 transition-colors duration-fast hover:bg-accent/40">
                            <td class="px-4 py-3 font-medium">{{ $form->title }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $badge = $form->status === 'published'
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-muted text-muted-foreground';
                                @endphp
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                                    {{ $form->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">v{{ $form->version }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $form->updated_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Builder --}}
                                    <a href="{{ route('forms.builder', $form) }}" class="btn btn-ghost btn-sm"
                                        title="Open Builder" aria-label="Open Builder">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                        </svg>
                                    </a>

                                    {{-- View --}}
                                    <a href="{{ route('forms.public', $form) }}" class="btn btn-ghost btn-sm"
                                        title="View Form" aria-label="View Form">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path
                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>

                                    {{-- Responses --}}
                                    <a href="{{ route('forms.submissions', $form) }}" class="btn btn-ghost btn-sm"
                                        title="View Responses" aria-label="View Responses">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z" />
                                            <path d="M8 10h8" />
                                            <path d="M8 14h5" />
                                        </svg>
                                    </a>

                                    {{-- AI --}}
                                    <a href="{{ route('forms.ai', $form) }}" class="btn btn-ghost btn-sm"
                                        title="AI Assistant" aria-label="AI Assistant">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M12 3v4" />
                                            <path d="M12 17v4" />
                                            <path d="M3 12h4" />
                                            <path d="M17 12h4" />
                                            <path d="m5.64 5.64 2.83 2.83" />
                                            <path d="m15.53 15.53 2.83 2.83" />
                                            <path d="m5.64 18.36 2.83-2.83" />
                                            <path d="m15.53 8.47 2.83-2.83" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>

                                    {{-- Share --}}
                                    <button @click="open('{{ route('forms.public', $form) }}')" class="btn btn-ghost btn-sm"
                                        title="Share Form" aria-label="Share Form">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <circle cx="18" cy="5" r="3" />
                                            <circle cx="6" cy="12" r="3" />
                                            <circle cx="18" cy="19" r="3" />
                                            <path d="m8.59 13.51 6.83 3.98" />
                                            <path d="m15.41 6.51-6.82 3.98" />
                                        </svg>
                                    </button>

                                    {{-- Delete --}}
                                    <button wire:click="delete({{ $form->id }})"
                                        wire:confirm="Delete this form permanently?"
                                        class="btn btn-ghost btn-sm text-destructive hover:bg-destructive/10"
                                        title="Delete Form" aria-label="Delete Form">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M3 6h18" />
                                            <path d="M8 6V4h8v2" />
                                            <path d="M19 6l-1 14H6L5 6" />
                                            <path d="M10 11v5" />
                                            <path d="M14 11v5" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-muted-foreground">No forms yet.
                                <a href="{{ route('forms.create') }}" class="btn-link ml-1">Create your first form</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $forms->links() }}</div>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @keydown.escape.window="open = false">
        <div class="panel w-full max-w-md p-6" @click.outside="open = false">
            <h2 class="mb-1 text-lg font-semibold">Share this form</h2>
            <p class="mb-4 text-sm text-muted-foreground">Anyone with the link can fill in the form.</p>

            <div class="mb-4 flex items-center gap-2">
                <input type="text" x-model="url" readonly class="input bg-muted">
                <button type="button" @click="copy()" class="btn btn-primary">Copy</button>
            </div>

            <div class="flex justify-center">
                <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(url)"
                    alt="QR code for this form" class="rounded-md border border-border" width="220" height="220">
            </div>

            <p class="mt-3 text-center text-xs text-muted-foreground">QR renders via qrserver.com (requires internet).
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('shareModal', () => ({
                open: false,
                url: '',

                open(url) {
                    this.url = url;
                    this.open = true;
                },

                async copy() {
                    if (navigator.clipboard) {
                        await navigator.clipboard.writeText(this.url);
                    }
                },
            }));
        });
    </script>

</div>