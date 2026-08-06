<div>
    @php $steps = $form->schema['steps'] ?? []; @endphp

    <div x-data="{ step: 1 }" class="max-w-3xl mx-auto py-10 px-4">

        <div class="bg-white rounded-lg shadow overflow-hidden">

            <div class="border-b px-6 py-5">
                <h1 class="text-2xl font-bold">{{ $form->title }}</h1>
                @if($form->description)
                    <p class="text-gray-500 mt-1">{{ $form->description }}</p>
                @endif
            </div>

            @if($submitted)
                <div class="p-8 text-center">
                    <div class="text-green-600 text-lg font-semibold mb-2">Form submitted successfully.</div>
                    <a href="{{ url()->current() }}" class="text-blue-600">Submit another response</a>
                </div>
            @else

            @if(count($steps) > 1)
                <div class="px-6 py-3 border-b flex flex-wrap gap-2">
                    @foreach($steps as $i => $step)
                        <button
                            type="button"
                            x-on:click="step = {{ $loop->iteration }}"
                            class="px-3 py-1 rounded text-sm bg-gray-200 text-gray-700"
                            x-bind:class="step === {{ $loop->iteration }} ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'">
                            {{ $step['title'] }}
                        </button>
                    @endforeach
                </div>
            @endif

            <form wire:submit="submit">

                @foreach($steps as $i => $step)
                    <section x-show="step === {{ $loop->iteration }}" x-cloak class="px-6 py-6 space-y-6">

                        @foreach($step['sections'] ?? [] as $section)
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 mb-3">{{ $section['title'] }}</h2>

                                <div class="space-y-5">
                                    @foreach($section['fields'] ?? [] as $field)
                                        @php $key = $field['key']; @endphp

                                        @if($field['type'] === 'heading')
                                            <h3 class="text-xl font-bold">{{ $field['label'] }}</h3>

                                        @elseif($field['type'] === 'section')
                                            <div class="border-t pt-4">
                                                <h4 class="font-medium text-gray-700">{{ $field['label'] }}</h4>
                                            </div>

                                        @else
                                            <div wire:key="f-{{ $key }}"
                                                 class="{{ $field['type'] === 'textarea' ? '' : 'max-w-md' }}">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                    {{ $field['label'] }}
                                                    @if(!empty($field['required']))<span class="text-red-500">*</span>@endif
                                                </label>

                                                @php
                                                    $attrs = '';
                                                    if (!empty($field['required'])) $attrs .= ' required';
                                                    if (($field['type'] ?? '') === 'email') $attrs .= ' type="email"';
                                                    $pattern = $field['regex'] ?? '';
                                                    if ($pattern) {
                                                        $pattern = preg_replace('#^/(.*)/([A-Za-z]*)$#', '$1', trim($pattern));
                                                        $attrs .= ' pattern="' . e($pattern) . '"';
                                                    }
                                                @endphp

                                                @if($field['type'] === 'textarea')
                                                    <textarea
                                                        wire:model="answers.{{ $key }}"
                                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                                        class="w-full border rounded px-3 py-2"{!! $attrs !!}></textarea>

                                                @elseif($field['type'] === 'dropdown')
                                                    <select
                                                        wire:model="answers.{{ $key }}"
                                                        class="w-full border rounded px-3 py-2" {!! $attrs !!}>
                                                        <option value="">-- Select --</option>
                                                        @foreach($field['options'] ?? [] as $option)
                                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                        @endforeach
                                                    </select>

                                                @elseif($field['type'] === 'radio')
                                                    <div class="space-y-1">
                                                        @foreach($field['options'] ?? [] as $option)
                                                            <label class="flex items-center gap-2">
                                                                <input
                                                                    type="radio"
                                                                    name="{{ $key }}"
                                                                    value="{{ $option['value'] }}"
                                                                    wire:model="answers.{{ $key }}"
                                                                    class="mr-2"
                                                                    @if(!empty($field['required'])) required @endif>
                                                                {{ $option['label'] }}
                                                            </label>
                                                        @endforeach
                                                    </div>

                                                @elseif($field['type'] === 'checkbox')
                                                    <div class="space-y-1">
                                                        @foreach($field['options'] ?? [] as $j => $option)
                                                            <label class="flex items-center gap-2">
                                                                <input
                                                                    type="checkbox"
                                                                    name="{{ $key }}"
                                                                    value="{{ $option['value'] }}"
                                                                    wire:model="answers.{{ $key }}.{{ $j }}"
                                                                    @if(!empty($field['required'])) required @endif>
                                                                {{ $option['label'] }}
                                                            </label>
                                                        @endforeach
                                                    </div>

                                                @elseif($field['type'] === 'rating')
                                                    <div class="flex gap-2">
                                                        @php $max = $field['max'] ?? 5; @endphp
                                                        @for($n = 1; $n <= (int)$max; $n++)
                                                            <label class="cursor-pointer text-2xl">
                                                                <input
                                                                    type="radio"
                                                                    name="{{ $key }}"
                                                                    value="{{ $n }}"
                                                                    wire:model="answers.{{ $key }}"
                                                                    class="sr-only"
                                                                    @if(!empty($field['required'])) required @endif>
                                                                ⭐
                                                            </label>
                                                        @endfor
                                                    </div>

                                                @elseif($field['type'] === 'file')
                                                    <input type="file" class="w-full border rounded px-3 py-2">

                                                @else
                                                    <input
                                                        type="{{ $field['type'] }}"
                                                        wire:model="answers.{{ $key }}"
                                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                                        class="w-full border rounded px-3 py-2"
                                                        @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                                                        @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                                                        @if(!empty($field['required'])) required @endif
                                                        {!! $attrs !!}>

                                                @endif

                                                @if(!empty($field['help']))
                                                    <p class="text-xs text-gray-400 mt-1">{{ $field['help'] }}</p>
                                                @endif

                                                @error("answers.{{ $key }}")
                                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </section>
                @endforeach

                @if(count($steps) > 1)
                    <div class="px-6 py-4 border-t flex justify-between">
                        <button type="button" x-on:click="step--" x-bind:disabled="step <= 1"
                            class="px-4 py-2 border rounded disabled:opacity-40">Back</button>
                        <button type="button" x-on:click="step++" x-show="step < {{ count($steps) }}"
                            class="px-4 py-2 bg-gray-200 rounded">Next</button>
                    </div>
                @endif

                <div class="px-6 py-4 border-t">
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded font-semibold">
                        Submit
                    </button>
                </div>

            </form>

            @endif

        </div>

    </div>
</div>