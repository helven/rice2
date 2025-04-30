<x-filament::page>
    <form wire:submit.prevent="save" class="fi-form grid gap-y-6" enctype="multipart/form-data">
        {{ $this->form }}

        <div class="fi-form-actions">
            <div class="fi-ac gap-3 flex flex-wrap items-center justify-center">
                <x-filament::button type="submit">
                    Save Changes
                </x-filament::button>

                <x-filament::button tag="a" href="/admin/orders" color="secondary">
                    Cancel
                </x-filament::button>
            </div>
        </div>
    </form>
</x-filament::page>