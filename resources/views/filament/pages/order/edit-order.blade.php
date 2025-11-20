<x-filament::page x-data="{ showModal: false }">
    <script>
        // Global variable to store disabled dates
        let disabledDates = [];
        const MEAL_PRICE = {{ config('app.meal_price', 8.00) }};
        
        function handleOrderMealQtyChange(input) {
            // Find the meals repeater container
            const mealsContainer = input.closest('[data-id="meals"]');
            const mealContainer = mealsContainer.querySelector('li');
            if (!mealsContainer || !mealContainer) return;

            // Find the order container
            const orderContainer = mealsContainer.closest('[data-id="meals_by_date"]');
            console.log(mealsContainer)
            console.log(orderContainer)
            if (!orderContainer) return;
            
            // Find all quantity inputs in this meal repeater
            const quantities = orderContainer.querySelectorAll('input[data-class="meal-qty"]');
            let total = 0;
            
            quantities.forEach(qty => {
                qty.value = parseInt(qty.value) || 0;
                const val = parseInt(qty.value);
                total += val;
            });
            
            // Calculate total amount
            const totalAmount = (total * MEAL_PRICE).toFixed(2);
            
            // Find the total_amount field in the parent container
            if (orderContainer) {
                const totalField = orderContainer.querySelector('input[data-id="total_amount"]');
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
                let url = `/api/orders/existing-delivery-dates?customer_id=${customerId}&address_id=${addressId}&order_type=single`;
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

                <x-filament::button wire:click="openModal">
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