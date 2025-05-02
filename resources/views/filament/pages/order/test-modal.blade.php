<x-filament::page
    x-data="{ show: false }"
>
    <x-filament::button x-on:click="$dispatch('open-modal', { id: 'test-modal' })">
        Open Test Modal
    </x-filament::button>

    <x-filament::modal
        id="test-modal"
        x-show="show"
        x-on:open-modal.window="if ($event.detail.id === 'test-modal') show = true"
        x-on:close-modal.window="if ($event.detail.id === 'test-modal') show = false"
        x-on:keydown.escape.window="show = false"
        x-trap.inert.noscroll="show"
        wire:ignore.self
    >
        <x-slot name="heading">
            Test Modal Header
        </x-slot>

        This is a test modal content

        <x-slot name="footerActions">
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'test-modal' })">
                Close
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament::page>