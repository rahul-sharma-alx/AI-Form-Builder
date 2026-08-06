<div>
    @php $steps = $form->schema['steps'] ?? []; @endphp

    <div class="max-w-3xl mx-auto py-10 px-4">

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

            <form wire:submit="submit">

                @foreach($steps as $i => $step)
                    <section class="px-6 py-6 space-y-6 {{ $i > 0 ? 'border-t' : '' }}">

                        @if(count($steps) > 1)
                            <h2 class="text-xl font-bold text-gray-800">{{ $step['title'] }}</h2>
                        @endif

                        @foreach($step['sections'] ?? [] as $section)
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">{{ $section['title'] }}</h3>

                                <div class="space-y-5">
                                    @foreach($section['fields'] ?? [] as $field)
                                        @php $key = $field['key']; @endphp

                                        @if($field['type'] === 'heading')
                                            <h4 class="text-xl font-bold">{{ $field['label'] }}</h4>

                                        @elseif($field['type'] === 'section')
                                            <div class="border-t pt-4">
                                                <h5 class="font-medium text-gray-700">{{ $field['label'] }}</h5>
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
                                                    $rules = $field['validation'] ?? [];
                                                    if (is_string($rules)) { $rules = explode('|', $rules); }
                                                    foreach ($rules as $rule) {
                                                        if (!is_string($rule)) continue;
                                                        $parts = explode(':', trim($rule));
                                                        if ($parts[0] === 'min' && isset($parts[1]) && in_array($field['type'], ['text', 'textarea', 'email', 'phone', 'number'], true)) {
                                                            $attrs .= ' minlength="' . e($parts[1]) . '"';
                                                        }
                                                        if ($parts[0] === 'max' && isset($parts[1]) && in_array($field['type'], ['text', 'textarea', 'email', 'phone', 'number'], true)) {
                                                            $attrs .= ' maxlength="' . e($parts[1]) . '"';
                                                        }
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