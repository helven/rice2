<x-filament::page x-data="{ showModal: false }">
    <script>
        // Global variable to store disabled dates
        let disabledDates = [];
        const MEAL_PRICE = {{ config('app.meal_price', 8.00) }};
        
        function handleMealQtyChange(input) {
            // Find the meals container
            const mealsContainer = input.closest('[data-id="meals"]');
            const mealContainer = mealsContainer.querySelector('li');
            if (!mealsContainer || !mealContainer) return;

            // Find the order container
            const orderContainer = mealsContainer.closest('li');
            if (!orderContainer) return;
            
            // Find all quantity inputs in this meal repeater
            const quantities = orderContainer.querySelectorAll('input[data-class="meal-qty"]');
            let total = 0;
            
            quantities.forEach(qty => {
                const val = parseInt(qty.value) || 0;
                total += val;
            });
            
            // Calculate total amount
            const totalAmount = (total * MEAL_PRICE).toFixed(2);console.log(totalAmount)
            
            // Find the total_amount field in the parent container
            if (orderContainer) {
                const totalField = orderContainer.querySelector('input[data-id="total_amount"]');
                if (totalField) {
                    totalField.value = totalAmount;
                    totalField.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        }
        
        // Function to fetch existing delivery dates for customer/address combination
        async function fetchExistingDeliveryDates(customerId, addressId) {
            if (!customerId || !addressId) {
                disabledDates = [];
                updateFlatpickrDisabledDates();
                return;
            }
            
            try {
                const response = await fetch(`/api/orders/existing-delivery-dates?customer_id=${customerId}&address_id=${addressId}&order_type=single`);
                const data = await response.json();
                disabledDates = data.dates || [];
                updateFlatpickrDisabledDates();
            } catch (error) {
                console.error('Error fetching existing delivery dates:', error);
                disabledDates = [];
                updateFlatpickrDisabledDates();
            }
        }
        
        // Function to update flatpickr with disabled dates
        function updateFlatpickrDisabledDates() {
            const deliveryDateInput = document.querySelector('#delivery_date');
            if (deliveryDateInput && deliveryDateInput._flatpickr) {
                const flatpickrInstance = deliveryDateInput._flatpickr;
                
                // Get current selected dates
                const currentSelectedDates = flatpickrInstance.selectedDates;
                
                // Convert disabled dates to Date objects for comparison
                const disabledDateObjects = disabledDates.map(date => new Date(date));
                
                // Filter out any selected dates that are now disabled
                const validSelectedDates = currentSelectedDates.filter(selectedDate => {
                    return !disabledDateObjects.some(disabledDate => 
                        selectedDate.toDateString() === disabledDate.toDateString()
                    );
                });
                
                // Update flatpickr with disabled dates
                flatpickrInstance.set('disable', disabledDateObjects);
                
                // If some dates were removed, update the selection
                if (validSelectedDates.length !== currentSelectedDates.length) {
                    flatpickrInstance.setDate(validSelectedDates, true);
                }
            }
        }
        
        // Function to setup event listeners for customer and address changes
        function setupDateDisabling() {
            // Wait for the DOM to be ready
            setTimeout(() => {
                const customerSelect = document.querySelector('[name="data.customer_id"]');
                const addressSelect = document.querySelector('[name="data.address_id"]');
                
                if (customerSelect) {
                    customerSelect.addEventListener('change', function() {
                        const customerId = this.value;
                        const addressId = addressSelect ? addressSelect.value : null;
                        fetchExistingDeliveryDates(customerId, addressId);
                    });
                }
                
                if (addressSelect) {
                    addressSelect.addEventListener('change', function() {
                        const addressId = this.value;
                        const customerId = customerSelect ? customerSelect.value : null;
                        fetchExistingDeliveryDates(customerId, addressId);
                    });
                }
                
                // Check for initial values
                const initialCustomerId = customerSelect ? customerSelect.value : null;
                const initialAddressId = addressSelect ? addressSelect.value : null;
                if (initialCustomerId && initialAddressId) {
                    fetchExistingDeliveryDates(initialCustomerId, initialAddressId);
                }
            }, 1000);
        }
        
        // Initialize when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            setupDateDisabling();
        });
        
        // Also setup when Livewire updates the page
        document.addEventListener('livewire:navigated', function() {
            setupDateDisabling();
        });
        
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
        
        function populateConfirmModal() {
            // Get form values
            const customerSelect = document.querySelector('[name="data.customer_id"]');
            const addressSelect = document.querySelector('[name="data.address_id"]');
            const deliveryDates = document.querySelector('[name="data.delivery_dates"]');
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
                if (deliveryCell && deliveryDates) {
                    deliveryCell.textContent = deliveryDates.value;
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
                                    $wire.createOrder(true);
                                    window.createAnotherAction = false;
                                } else {
                                    $wire.createOrder(false);
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