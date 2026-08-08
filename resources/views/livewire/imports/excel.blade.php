<div>

    <div class="max-w-4xl mx-auto">

        <a href="{{ route('forms.index') }}" class="btn-link text-sm">&larr; Back to forms</a>

        <h1 class="mt-2 text-2xl font-bold tracking-tight mb-2">Import from Excel</h1>
        <p class="text-sm text-muted-foreground mb-6">Upload an .xlsx, .xls or .csv file where each row is a question. Preview the data, map your columns, and the import runs in the background.</p>

        @if(! $this->importId)

            <div class="card p-6">
                <form wire:submit="upload">
                    <label class="label">Spreadsheet file</label>
                    <input type="file" wire:model="file" accept=".xlsx,.xls,.csv" class="input">
                    @error('file')
                        <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                    @enderror

                    <button type="submit" wire:loading.attr="disabled" class="btn btn-primary mt-4">Upload &amp; Preview</button>
                </form>

                <p class="mt-4 text-xs text-muted-foreground">
                    Don't have a file yet?
                    <a href="{{ route('imports.xlsx.template') }}" class="btn-link">Download the template</a> — columns:
                    type, label, required, placeholder, help, options, section, validation.
                </p>
            </div>

        @elseif($status === 'processing')

            <div wire:poll.2s class="card p-8 text-center">
                @if($this->import->status === 'completed')
                    <div class="mb-2 text-lg font-semibold text-emerald-600">Import complete!</div>
                    <p class="mb-4 text-sm text-muted-foreground">Your form was created from {{ count($this->import->parsed_schema['steps'][0]['sections'] ?? []) }} section(s).</p>
                    <a href="{{ route('forms.builder', $this->import->form) }}" class="btn btn-primary">Open in Builder</a>

                @elseif($this->import->status === 'failed')
                    <div class="mb-2 text-lg font-semibold text-destructive">Import failed</div>
                    <p class="mb-4 text-sm text-muted-foreground">{{ $this->import->error_message }}</p>
                    <button wire:click="$set('status', 'preview')" class="btn btn-primary">Back to Mapping</button>

                @else
                    <div class="mb-2 text-lg font-semibold">Importing your questions...</div>
                    <p class="text-sm text-muted-foreground">The file is being processed in the background. This page updates automatically.</p>
                @endif
            </div>

        @elseif($this->import->status === 'failed')

            <div class="card p-8">
                <div class="mb-2 text-lg font-semibold text-destructive">Upload failed</div>
                <p class="mb-4 text-sm text-muted-foreground">{{ $this->import->error_message }}</p>
                <button wire:click="$set('importId', null)" class="btn btn-primary">Try Another File</button>
            </div>

        @elseif($status === 'preview')

            <form wire:submit="createForm" class="card mb-6 p-6">

                <div class="mb-6 flex flex-wrap items-end gap-4">
                    <div class="min-w-64 flex-1">
                        <label class="label">Form title</label>
                        <input wire:model="title" class="input">
                        @error('title')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                    <label class="flex items-center gap-2 pb-2 text-sm text-foreground">
                        <input type="checkbox" wire:model="hasHeader" class="h-4 w-4 rounded accent-primary">
                        First row is a header
                    </label>
                </div>

                <div class="mb-6">
                    <h2 class="mb-2 text-lg font-semibold">Map your columns</h2>
                    <p class="mb-3 text-xs text-muted-foreground">Assign each column to a form field property, or leave it ignored. At least one column must map to <b>label</b>.</p>
                    @error('mapping')
                        <p class="mb-2 text-sm text-destructive">{{ $message }}</p>
                    @enderror

                    <div class="space-y-2">
                        @foreach($columns as $column)
                            <div class="flex items-center gap-3">
                                <span class="w-8 font-mono font-semibold text-muted-foreground">{{ $column }}</span>
                                <span class="w-40 truncate text-sm text-muted-foreground" title="{{ $this->previewRows[0][$column] ?? '' }}">
                                    {{ $this->previewRows[0][$column] ?? '(empty)' }}
                                </span>
                                <select wire:model="mapping.{{ $column }}" class="input w-auto flex-1">
                                    @foreach($this->properties as $property)
                                        <option value="{{ $property }}">{{ $property }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>

                <h2 class="mb-2 text-lg font-semibold">Preview</h2>
                <div class="mb-6 overflow-x-auto rounded-md border border-border">
                    <table class="w-full text-sm">
                        <tbody>
                            @foreach($previewRows as $rowIndex => $row)
                                <tr class="{{ $hasHeader && $rowIndex === 0 ? 'bg-accent/50 font-semibold' : 'border-t border-border' }}">
                                    @foreach($columns as $column)
                                        <td class="px-3 py-1.5 text-foreground">{{ $row[$column] ?? '' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary">Import &amp; Create Form</button>
            </form>

        @endif

    </div>

</div>
