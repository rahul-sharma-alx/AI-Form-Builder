<div x-data="builderAutosave">
    {{-- Top Toolbar --}}
    <div class="bg-white p-4 rounded shadow mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[240px]">
                <label class="block text-xs text-gray-500 mb-1">Title</label>
                <input
                    type="text"
                    wire:model.live.debounce.500ms="title"
                    x-on:input="dirty()"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Form title"
                />
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Description</label>
                <input
                    type="text"
                    wire:model.live.debounce.500ms="description"
                    x-on:input="dirty()"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Form description"
                />
            </div>

            <div class="flex flex-col items-end gap-1">
                <div class="flex items-center gap-2">
                    @php $published = $form->isPublished(); @endphp
                    @if($published)
                        <button wire:click="unpublish" class="bg-amber-500 text-white px-3 py-2 rounded text-sm">
                            Unpublish
                        </button>
                    @else
                        <button wire:click="publish" class="bg-emerald-600 text-white px-3 py-2 rounded text-sm">
                            Publish
                        </button>
                    @endif
                    <button wire:click="save" class="bg-blue-600 text-white px-3 py-2 rounded text-sm">
                        Save
                    </button>
                </div>
                <div class="text-xs text-gray-500 flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full
                        {{ $published ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-600' }}">
                        {{ $published ? 'Published' : 'Draft' }}
                    </span>
                    <span>v{{ $version }}</span>
                </div>
            </div>
        </div>

        <div class="mt-3 flex items-center gap-2 text-sm">
            <span class="inline-flex items-center gap-1" x-show="saving">
                <svg class="animate-spin h-4 w-4 text-blue-600" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Saving…
            </span>
            <span x-show="!saving && unsaved" class="text-amber-600">Unsaved changes</span>
            <span x-show="!saving && !unsaved" class="text-gray-500">
                Saved <span x-text="savedAt"></span>
            </span>
            @if(session()->has('success'))
                <span class="text-green-600">{{ session('success') }}</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        {{-- Left Panel: Field Library --}}
        <div class="col-span-2 bg-white p-4 rounded shadow">
            <livewire:builder.palette />
        </div>

        {{-- Center Panel: Canvas --}}
        <div class="col-span-7 bg-white p-4 rounded shadow">
            <livewire:builder.canvas
                :schema="$schema"
                :current-step-id="$currentStepId"
                :current-section-id="$currentSectionId"
                :selected-field-id="$selectedFieldId"
            />
        </div>

        {{-- Right Panel: Properties --}}
        <div class="col-span-3 bg-white p-4 rounded shadow">
            <livewire:builder.property-panel />
        </div>

        {{-- JSON Editor --}}
        <div class="col-span-12 mt-6 bg-white p-4 rounded shadow">
            <livewire:builder.json-editor :schema="$schema" />
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('builderAutosave', () => ({
                unsaved: false,
                saving: false,
                savedAt: @js($savedAt),
                timer: null,

                init() {
                    Livewire.on('content-dirty', () => this.dirty());
                },

                dirty() {
                    this.unsaved = true;
                    clearTimeout(this.timer);
                    this.timer = setTimeout(() => this.save(), 2000);
                },

                async save() {
                    this.saving = true;
                    try {
                        const result = await $wire.autosave();
                    } catch (e) {
                        // surface error via unsaved state
                        this.unsaved = true;
                    }
                    this.saving = false;
                    this.unsaved = false;
                },
            }));
        });
    </script>
</div>