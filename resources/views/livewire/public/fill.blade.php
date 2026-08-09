<div>
    @php $steps = $form->schema['steps'] ?? []; @endphp

    <div class="max-w-3xl mx-auto">

        <div class="panel overflow-hidden">

            <div class="border-b border-border bg-accent/30 px-6 py-5">
                <h1 class="text-2xl font-bold tracking-tight">{{ $form->title }}</h1>
                @if($form->description)
                    <p class="mt-1 text-sm text-muted-foreground">{{ $form->description }}</p>
                @endif
            </div>

            @if($submitted)
                <div class="p-8 text-center">
                    <div class="mb-2 text-lg font-semibold text-emerald-600">Form submitted successfully.</div>
                    <a href="{{ url()->current() }}" class="btn-link text-sm">Submit another response</a>
                </div>
            @else

            <form wire:submit="submit">

                @foreach($steps as $i => $step)
                    <section class="px-6 py-6 space-y-6 {{ $i > 0 ? 'border-t' : '' }}">

                        @if(count($steps) > 1)
                            <h2 class="text-xl font-bold text-foreground">{{ $step['title'] }}</h2>
                        @endif

                        @foreach($step['sections'] ?? [] as $section)
                            <div>
                                <h3 class="mb-3 text-lg font-semibold text-foreground">{{ $section['title'] }}</h3>

                                <div class="space-y-5">
                                    @foreach($section['fields'] ?? [] as $field)
                                        @php $key = $field['key']; @endphp

                                        @if($field['type'] === 'heading')
                                            <h4 class="text-xl font-bold">{{ $field['label'] }}</h4>

                                        @elseif($field['type'] === 'section')
                                            <div class="border-t pt-4">
                                                <h5 class="font-medium text-foreground">{{ $field['label'] }}</h5>
                                            </div>

                                        @elseif($field['type'] === 'html')
                                            <div class="html-block text-sm leading-relaxed [&_a]:text-primary [&_a]:underline">{!! $field['content'] ?? '' !!}</div>

                                        @else
                                            @if(\App\Support\SchemaConditions::visible($field['visibility'] ?? null, $answers))
                                            <div wire:key="f-{{ $key }}"
                                                 class="{{ $field['type'] === 'textarea' ? '' : 'max-w-md' }}">
                                                <label class="label">
                                                    {{ $field['label'] }}
                                                    @if(!empty($field['required']))<span class="text-destructive">*</span>@endif
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
                                                        wire:model.live="answers.{{ $key }}"
                                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                                        class="input py-2"{!! $attrs !!}></textarea>

                                                @elseif($field['type'] === 'dropdown')
                                                    <select
                                                        wire:model.live="answers.{{ $key }}"
                                                        class="input"{!! $attrs !!}>
                                                        <option value="">-- Select --</option>
                                                        @foreach($field['options'] ?? [] as $option)
                                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                        @endforeach
                                                    </select>

                                                @elseif($field['type'] === 'radio')
                                                    <div class="space-y-1">
                                                        @foreach($field['options'] ?? [] as $option)
                                                            <label class="flex items-center gap-2 text-sm">
                                                                <input
                                                                    type="radio"
                                                                    name="{{ $key }}"
                                                                    value="{{ $option['value'] }}"
                                                                    wire:model.live="answers.{{ $key }}"
                                                                    class="h-4 w-4 accent-primary"
                                                                    @if(!empty($field['required'])) required @endif>
                                                                {{ $option['label'] }}
                                                            </label>
                                                        @endforeach
                                                    </div>

                                                @elseif($field['type'] === 'checkbox')
                                                    <div class="space-y-1">
                                                        @foreach($field['options'] ?? [] as $j => $option)
                                                            <label class="flex items-center gap-2 text-sm">
                                                                <input
                                                                    type="checkbox"
                                                                    name="{{ $key }}"
                                                                    value="{{ $option['value'] }}"
                                                                    wire:model.live="answers.{{ $key }}.{{ $j }}"
                                                                    class="h-4 w-4 rounded accent-primary"
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
                                                                    wire:model.live="answers.{{ $key }}"
                                                                    class="sr-only"
                                                                    @if(!empty($field['required'])) required @endif>
                                                                ⭐
                                                            </label>
                                                        @endfor
                                                    </div>

                                                @elseif($field['type'] === 'file')
                                                    <input type="file" class="input">

                                                @else
                                                    <input
                                                        type="{{ $field['type'] }}"
                                                        wire:model.live="answers.{{ $key }}"
                                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                                        class="input"
                                                        @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                                                        @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                                                        @if(!empty($field['required'])) required @endif
                                                        {!! $attrs !!}>

                                                @endif

                                                @if(!empty($field['help']))
                                                    <p class="mt-1 text-xs text-muted-foreground">{{ $field['help'] }}</p>
                                                @endif

                                                @error("answers.{{ $key }}")
                                                    <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </section>
                @endforeach

                <div class="border-t border-border px-6 py-4">
                    @error('_form')
                        <p class="mb-2 text-sm text-destructive">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="btn btn-primary w-full h-11 text-base">
                        Submit
                    </button>
                </div>

            </form>

            @endif

        </div>

    </div>
</div>