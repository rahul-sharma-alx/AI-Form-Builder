<div>

    <h1 class="text-3xl mb-6">

        Create Form

    </h1>

    <form wire:submit="save">

        <div class="mb-4">

            <input type="text" wire:model="title" placeholder="Form Title" class="w-full border p-2">

            @error('title')

                <div class="text-red-500">

                    {{ $message }}

                </div>

            @enderror

        </div>

        <div class="mb-4">

            <textarea wire:model="description" class="w-full border p-2">
</textarea>

        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded">

            Save Draft

        </button>

    </form>

</div>