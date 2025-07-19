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
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                const formatDate = (date) => {
                    return date.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });
                };
                
                if (startDate === endDate) {
                    return formatDate(start);
                } else {
                    return `${formatDate(start)}, ${formatDate(end)}`;
                }
            } catch (error) {
                return dateRange;
            }
        }
        
        function populateConfirmModal() {
            const modal = document.querySelector('.confirm-modal');
            if (!modal) return;
            
            // Get form data
            const form = document.querySelector('form[wire\\:submit="save"]');
            if (!form) return;
            
            // Customer Name
            const customerSelect = form.querySelector('select[name="customer_id"]');
            const customerCell = findTableCellByText(modal, 'Customer:');
            if (customerCell && customerSelect) {
                const selectedOption = customerSelect.options[customerSelect.selectedIndex];
                customerCell.textContent = selectedOption ? selectedOption.text : '';
            }
            
            // Address
            const addressSelect = form.querySelector('select[name="address_id"]');
            const addressCell = findTableCellByText(modal, 'Address:');
            if (addressCell && addressSelect) {
                const selectedOption = addressSelect.options[addressSelect.selectedIndex];
                addressCell.textContent = selectedOption ? selectedOption.text : '';
            }
            
            // Delivery Date
            const deliveryDateInput = form.querySelector('input[name="delivery_date"]');
            const deliveryDateCell = findTableCellByText(modal, 'Delivery Date:');
            if (deliveryDateCell && deliveryDateInput) {
                deliveryDateCell.textContent = formatDeliveryDateRange(deliveryDateInput.value);
            }
            
            // Arrival Time
            const arrivalTimeInput = form.querySelector('input[name="arrival_time"]');
            const arrivalTimeCell = findTableCellByText(modal, 'Arrival Time:');
            if (arrivalTimeCell && arrivalTimeInput && arrivalTimeInput.value) {
                try {
                    const time24 = arrivalTimeInput.value;
                    const [hours, minutes] = time24.split(':');
                    const date = new Date();
                    date.setHours(parseInt(hours), parseInt(minutes));
                    const time12 = date.toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                    arrivalTimeCell.textContent = time12;
                } catch (error) {
                    arrivalTimeCell.textContent = arrivalTimeInput.value;
                }
            }
            
            // Driver
            const driverSelect = form.querySelector('select[name="driver_id"]');
            const driverCell = findTableCellByText(modal, 'Driver:');
            if (driverCell && driverSelect) {
                const selectedOption = driverSelect.options[driverSelect.selectedIndex];
                driverCell.textContent = selectedOption ? selectedOption.text : '';
            }
            
            // Route
            const routeInput = form.querySelector('input[name="route"]');
            const routeCell = findTableCellByText(modal, 'Route:');
            if (routeCell && routeInput) {
                routeCell.textContent = routeInput.value || '';
            }
            
            // Backup Driver
            const backupDriverSelect = form.querySelector('select[name="backup_driver_id"]');
            const backupDriverCell = findTableCellByText(modal, 'Backup Driver:');
            if (backupDriverCell && backupDriverSelect) {
                const selectedOption = backupDriverSelect.options[backupDriverSelect.selectedIndex];
                backupDriverCell.textContent = selectedOption ? selectedOption.text : '';
            }
            
            // Backup Route
            const backupRouteInput = form.querySelector('input[name="backup_route"]');
            const backupRouteCell = findTableCellByText(modal, 'Backup Route:');
            if (backupRouteCell && backupRouteInput) {
                backupRouteCell.textContent = backupRouteInput.value || '';
            }
            
            // Driver Notes
            const driverNotesTextarea = form.querySelector('textarea[name="driver_notes"]');
            const driverNotesCell = findTableCellByText(modal, 'Driver Notes:');
            if (driverNotesCell && driverNotesTextarea) {
                driverNotesCell.textContent = driverNotesTextarea.value || '';
            }
        }
    </script>
    
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
                        type="submit"
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