<x-filament::page>
    <form wire:submit="create" class="fi-form grid gap-y-6" enctype="multipart/form-data">
        {{ $this->form }}

        <div class="fi-form-actions">
            <div class="fi-ac gap-3 flex flex-wrap items-center justify-center">
                <x-filament::button
                    type="submit"
                    class="mt-4"
                >
                    Submit
                </x-filament::button>

                <x-filament::button tag="a" href="/admin/orders" color="gray">
                    Cancel
                </x-filament::button>
            </div>
        </div>
    </form>
</x-filament::page>
