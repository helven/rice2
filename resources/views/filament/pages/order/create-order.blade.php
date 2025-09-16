<x-filament::page x-data="{ showModal: false }">
    <script>
        function findTableCellByText(container, labelText) {
            const rows = container.querySelectorAll('tr');
            for (let row of rows) {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 2 && cells[0].textContent.trim() === labelText) {
                    return cells[1];
                }
            }
            return null;
        }
        
        function formatDeliveryDateRange(dateRange) {
            if (!dateRange) return '';
            try {
                const [startDate, endDate] = dateRange.split(' - ');
                const start = new Date(startDate.replace(/\//g, '-'));
                const end = new Date(endDate.replace(/\//g, '-'));
                
                const dates = [];
                for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                    dates.push(d.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }));
                }
                return dates.join(', ');
            } catch (e) {
                return dateRange;
            }
        }
        
        function populateConfirmModal() {
            // Get form values
            const customerSelect = document.querySelector('[name="data.customer_id"]');
            const addressSelect = document.querySelector('[name="data.address_id"]');
            const deliveryDateRange = document.querySelector('[name="data.delivery_date_range"]');
            const arrivalTime = document.querySelector('[name="data.arrival_time"]');
            const driverSelect = document.querySelector('[name="data.driver_id"]');
            const driverRoute = document.querySelector('[name="data.driver_route"]');
            const backupDriverSelect = document.querySelector('[name="data.backup_driver_id"]');

            const driverNotes = document.querySelector('[name="data.driver_notes"]');
            
            // Update modal content with form values
            const modal = document.querySelector('#confirm-modal');
            if (modal) {
                // Update customer name
                const customerName = customerSelect?.selectedOptions[0]?.text || '';
                const customerCell = findTableCellByText(modal, 'Customer:');
                if (customerCell) customerCell.textContent = customerName;
                
                // Update address (get selected option text)
                const addressName = addressSelect?.selectedOptions[0]?.text || '';
                const addressCell = findTableCellByText(modal, 'Address:');
                if (addressCell) addressCell.innerHTML = addressName;
                
                // Update delivery date
                const deliveryCell = findTableCellByText(modal, 'Delivery Date:');
                if (deliveryCell && deliveryDateRange) {
                    deliveryCell.textContent = formatDeliveryDateRange(deliveryDateRange.value);
                }
                
                // Update arrival time
                const arrivalCell = findTableCellByText(modal, 'Arrival Time:');
                if (arrivalCell && arrivalTime) {
                    const timeValue = arrivalTime.value;
                    if (timeValue) {
                        try {
                            const time12 = new Date(`1970-01-01T${timeValue}:00`).toLocaleTimeString('en-US', {
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                            });
                            arrivalCell.textContent = time12;
                        } catch (e) {
                            arrivalCell.textContent = timeValue;
                        }
                    }
                }
                
                // Update driver name
                const driverName = driverSelect?.selectedOptions[0]?.text || '';
                const driverCell = findTableCellByText(modal, 'Driver:');
                if (driverCell) driverCell.textContent = driverName;
                
                // Update driver route
                const routeCell = findTableCellByText(modal, 'Route:');
                if (routeCell && driverRoute) {
                    routeCell.textContent = driverRoute.value;
                }
                
                // Update backup driver if exists
                const backupDriverName = backupDriverSelect?.selectedOptions[0]?.text || '';
                const backupDriverCell = findTableCellByText(modal, 'Backup Driver:');
                if (backupDriverCell) backupDriverCell.textContent = backupDriverName;
                

                
                // Update driver notes
                const notesCell = findTableCellByText(modal, 'Driver Notes:');
                if (notesCell && driverNotes) {
                    notesCell.textContent = driverNotes.value;
                }
            }
        }
    </script>
    <form class="fi-form grid gap-y-6" enctype="multipart/form-data">
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
                        () => {
                            // Client-side form validation
                            const form = document.querySelector('form');
                            if (form.checkValidity()) {
                                // Populate modal with form data
                                populateConfirmModal();
                                $dispatch('open-modal', { id: 'confirm-modal' });
                            } else {
                                // Show validation errors
                                form.reportValidity();
                            }
                        }
                    "
                >
                    Save
                </x-filament::button>

                <x-filament::button
                    x-on:click="
                        () => {
                            // Client-side form validation
                            const form = document.querySelector('form');
                            if (form.checkValidity()) {
                                // Set a flag to indicate 'Save & Create Another' action
                                window.createAnotherAction = true;
                                // Populate modal with form data
                                populateConfirmModal();
                                $dispatch('open-modal', { id: 'confirm-modal' });
                            } else {
                                // Show validation errors
                                form.reportValidity();
                            }
                        }
                    "
                    color="gray"
                >
                    Save & Create another
                </x-filament::button>

                <x-filament::button tag="a" href="/backend/orders" color="gray">
                    Cancel
                </x-filament::button>
            </div>
        </div>

        <x-filament::modal
            id="confirm-modal"
            width="5xl"
            x-show="showModal"
            x-on:open-modal.window="if ($event.detail.id === 'confirm-modal') showModal = true"
            x-on:close-modal.window="if ($event.detail.id === 'confirm-modal') showModal = false"
            x-on:keydown.escape.window="showModal = false"
            x-trap.inert.noscroll="showModal"
            wire:ignore.self
        >
            <x-slot name="heading">
                <div class="text-xl">Confirm Order</div>
            </x-slot>

            @include('filament.pages.order.partials.confirm-modal')

            <x-slot name="footerActions">
                <div class="w-full gap-3 flex flex-wrap items-center justify-center">
                    <x-filament::button
                        x-on:click="
                            () => {
                                if (window.createAnotherAction) {
                                    $wire.createAnother();
                                    window.createAnotherAction = false;
                                } else {
                                    $wire.create();
                                }
                                $dispatch('close-modal', { id: 'confirm-modal' });
                            }
                        "
                        class="mt-4"
                    >
                        Submit
                    </x-filament::button>
                    <x-filament::button x-on:click="$dispatch('close-modal', { id: 'confirm-modal' })" color="gray">
                        Close
                    </x-filament::button>
                </div>
            </x-slot>
        </x-filament::modal>
    </form>
</x-filament::page>