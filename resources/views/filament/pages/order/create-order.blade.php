<x-filament::page>
    <form wire:submit="create" class="fi-form grid gap-y-6" enctype="multipart/form-data">
        {{ $this->form }}

        <div class="fi-form-actions">
            <div class="fi-ac gap-3 flex flex-wrap items-center justify-start">
                <x-filament::button
                    type="submit"
                    class="mt-4"
                >
                    Create Order
                </x-filament::button>
            </div>
        </div>
    </form>
</x-filament::page>
