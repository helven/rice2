<x-filament::page x-data="{ showModal: false }">
    <form wire:submit="save" class="fi-form grid gap-y-6" enctype="multipart/form-data">
        {{ $this->form }}

        <div class="fi-form-actions">
            <div class="fi-ac gap-3 flex flex-wrap items-center justify-center">
                <?php /*<x-filament::button
                    type="submit"
                    class="mt-4"
                >
                    Submit
                </x-filament::button>*/ ?>

                <x-filament::button
                    x-on:click="
                        async () => {
                            validForm = false;
                            try {
                                const isValid = await $wire.validate();
                                if (isValid) {
                                    validForm = true;
                                }
                            } catch (error) {
                                validForm = false;
                            } finally {}
                            if(validForm) {
                                $dispatch('open-modal', { id: 'confirm-modal' });
                            }
                        }
                    "
                >
                    Save
                </x-filament::button>

                <x-filament::button tag="a" href="/admin/orders" color="gray">
                    Cancel
                </x-filament::button>
            </div>
        </div>

        <x-filament::modal
            id="confirm-modal"
            x-show="showModal"
            x-on:open-modal.window="if ($event.detail.id === 'confirm-modal') showModal = true"
            x-on:close-modal.window="if ($event.detail.id === 'confirm-modal') showModal = false"
            x-on:keydown.escape.window="showModal = false"
            x-trap.inert.noscroll="showModal"
            wire:ignore.self
        >
            <x-slot name="heading">
                Test Modal Header
            </x-slot>

            This is a test modal content

            <x-slot name="footerActions">
                <x-filament::button
                    type="submit"
                    class="mt-4"
                >
                    Submit
                </x-filament::button>
                <x-filament::button x-on:click="$dispatch('close-modal', { id: 'confirm-modal' })" color="gray">
                    Close
                </x-filament::button>
            </x-slot>
        </x-filament::modal>
    </form>
</x-filament::page>