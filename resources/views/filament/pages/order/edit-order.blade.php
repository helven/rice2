<x-filament::page x-data="{ showModal: false }">
    <script>
        // Global variable to store disabled dates
        let disabledDates = [];
        const MEAL_PRICE = {{ config('app.meal_price', 8.00) }};
        
        function handleMealQtyChange(input) {
            // Find the meals repeater container
            const mealRepeater = input.closest('[data-id="meals"]');
            if (!mealRepeater) return;
            
            // Find all quantity inputs in this meal repeater
            const quantities = mealRepeater.querySelectorAll('input[data-class="meal-qty"]');
            let total = 0;
            
            quantities.forEach(qty => {
                const val = parseInt(qty.value) || 0;
                total += val;
            });
            
            // Calculate total amount
            const totalAmount = (total * MEAL_PRICE).toFixed(2);
            
            // Find the total_amount field in the parent container
            const parentContainer = mealRepeater.closest('[data-id="meals_by_date"]');
            if (parentContainer) {
                const totalField = parentContainer.querySelector('input[data-id="total_amount"]');
                if (totalField) {
                    totalField.value = totalAmount;
                    totalField.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        }
        
        // Function to fetch existing delivery dates for customer/address combination (excluding current order)
        async function fetchExistingDeliveryDates(customerId, addressId, excludeOrderId = null) {
            if (!customerId || !addressId) {
                disabledDates = [];
                updateFlatpickrDisabledDates();
                return;
            }
            
            try {
                let url = `/api/orders/existing-delivery-dates?customer_id=${customerId}&address_id=${addressId}`;
                if (excludeOrderId) {
                    url += `&exclude_order_id=${excludeOrderId}`;
                }
                
                const response = await fetch(url);
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
            // Get the current order ID from the URL with multiple fallback methods
            let currentOrderId = null;
            
            // Method 1: Extract from URL path (most common)
            const pathParts = window.location.pathname.split('/');
            const ordersIndex = pathParts.indexOf('orders');
            if (ordersIndex !== -1 && pathParts[ordersIndex + 1]) {
                currentOrderId = pathParts[ordersIndex + 1];
            }
            
            // Method 2: Try to find order ID in hidden input or data attribute
            if (!currentOrderId) {
                const orderIdInput = document.querySelector('input[name="data.id"]') || 
                                   document.querySelector('[data-order-id]');
                if (orderIdInput) {
                    currentOrderId = orderIdInput.value || orderIdInput.getAttribute('data-order-id');
                }
            }
            
            // Method 3: Extract from URL parameters
            if (!currentOrderId) {
                const urlParams = new URLSearchParams(window.location.search);
                currentOrderId = urlParams.get('id') || urlParams.get('order_id');
            }
            
            // Function to set up event listeners
            function setupEventListeners() {
                const customerSelect = document.querySelector('#data\\.customer_id');
                const addressSelect = document.querySelector('#data\\.address_id');
                
                // Set up event listeners if elements exist
                if (customerSelect && !customerSelect.hasAttribute('data-date-listener')) {
                    customerSelect.setAttribute('data-date-listener', 'true');
                    customerSelect.addEventListener('change', function() {
                        const customerId = this.value;
                        const addressId = addressSelect ? addressSelect.value : null;
                        fetchExistingDeliveryDates(customerId, addressId, currentOrderId);
                    });
                }

                if (addressSelect && !addressSelect.hasAttribute('data-date-listener')) {
                    addressSelect.setAttribute('data-date-listener', 'true');
                    addressSelect.addEventListener('change', function() {
                        const addressId = this.value;
                        const customerId = customerSelect ? customerSelect.value : null;
                        fetchExistingDeliveryDates(customerId, addressId, currentOrderId);
                    });
                }
            }
            
            // Function to check for initial values with retry mechanism
            function checkInitialValues(retryCount = 0) {
                const customerSelect = document.querySelector('#data\\.customer_id');
                const addressSelect = document.querySelector('#data\\.address_id');
                
                const initialCustomerId = customerSelect ? customerSelect.value : null;
                const initialAddressId = addressSelect ? addressSelect.value : null;
                
                if (initialCustomerId && initialAddressId) {
                    // Both values are present, fetch disabled dates
                    fetchExistingDeliveryDates(initialCustomerId, initialAddressId, currentOrderId);
                } else if (retryCount < 10) {
                    // Values not ready yet, retry after a short delay
                    setTimeout(() => checkInitialValues(retryCount + 1), 1000);
                }
            }
            
            // Set up event listeners first
            setTimeout(() => setupEventListeners(), 500);
            
            // Then check for initial values
            setTimeout(() => checkInitialValues(), 1000);
        }
        
        // Initialize when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            setupDateDisabling();
        });
        
        // Also setup when Livewire updates the page
        document.addEventListener('livewire:navigated', function() {
            setupDateDisabling();
        });
        
        // Listen for Livewire component updates (when form data is loaded)
        document.addEventListener('livewire:updated', function() {
            setupDateDisabling();
        });
        
        // Listen for Alpine.js initialization (Filament uses Alpine)
        document.addEventListener('alpine:init', function() {
            setTimeout(() => setupDateDisabling(), 1000);
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