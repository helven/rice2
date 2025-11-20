<x-filament::page x-data="{ showModal: false }">
    <script>
        // Global variable to store disabled dates
        let disabledDates = [];
        const MEAL_PRICE = {{ config('app.meal_price', 8.00) }};
        
        function handleDeliveryDateChange(el) {
            const dayCountInput = document.querySelector("#day_count");
            const dateEle = document.querySelector("#delivery_date");
            if (dateEle.value && dayCountInput) {
                const dates = dateEle.value.split(",").filter(d => d.trim());
                const count = dates.length || 1;
                dayCountInput.value = count;
                dayCountInput.dispatchEvent(new Event("input", {bubbles: true}));

                // Update Total
                const mealsContainers = document.querySelectorAll('[data-id="meals"]');

                mealsContainers.forEach(mealsContainer => {
                    const quantities = mealsContainer.querySelectorAll('input[data-class="meal-qty"]');

                    let total = 0;

                    quantities.forEach(qty => {
                        const val = parseInt(qty.value) || 0;
                        total += val;
                    });
                    
                    calculateTotalAmount(total);
                });
            }
        }
        
        function handleMealPlanMealQtyChange(input) {
            const mealsContainer = input.closest('[data-id="meals"]');
            if (!mealsContainer) return;
            
            const quantities = mealsContainer.querySelectorAll('input[data-class="meal-qty"]');
            let total = 0;
            
            quantities.forEach(qty => {
                qty.value = parseInt(qty.value) || 0;
                const val = qty.value;
                total += val;
            });
            
            calculateTotalAmount(total);
        }

        function calculateTotalAmount(total) {
            // Get number of days from day_count field
            const dayCountField = document.querySelector('[data-id="day_count"] input');
            const dayCount = dayCountField ? parseInt(dayCountField.value) || 1 : 1;
            
            const totalAmount = (total * MEAL_PRICE * dayCount).toFixed(2);
            
            const totalField = document.querySelector('input[data-id="total_amount"]');
            if (totalField) {
                totalField.value = totalAmount;
                totalField.dispatchEvent(new Event('input', { bubbles: true }));
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
                let url = `/api/orders/existing-delivery-dates?customer_id=${customerId}&address_id=${addressId}&order_type=meal_plan`;
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
            // Get the current order ID from the URL
            let currentOrderId = null;
            const pathParts = window.location.pathname.split('/');
            const ordersIndex = pathParts.indexOf('orders');
            if (ordersIndex !== -1 && pathParts[ordersIndex + 1]) {
                currentOrderId = pathParts[ordersIndex + 1];
            }
            
            setTimeout(() => {
                const customerSelect = document.querySelector('[name="data.customer_id"]');
                const addressSelect = document.querySelector('[name="data.address_id"]');
                
                if (customerSelect) {
                    customerSelect.addEventListener('change', function() {
                        const customerId = this.value;
                        const addressId = addressSelect ? addressSelect.value : null;
                        fetchExistingDeliveryDates(customerId, addressId, currentOrderId);
                    });
                }
                
                if (addressSelect) {
                    addressSelect.addEventListener('change', function() {
                        const addressId = this.value;
                        const customerId = customerSelect ? customerSelect.value : null;
                        fetchExistingDeliveryDates(customerId, addressId, currentOrderId);
                    });
                }
                
                // Check for initial values
                const initialCustomerId = customerSelect ? customerSelect.value : null;
                const initialAddressId = addressSelect ? addressSelect.value : null;
                if (initialCustomerId && initialAddressId) {
                    fetchExistingDeliveryDates(initialCustomerId, initialAddressId, currentOrderId);
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
        
        // Listen for Livewire component updates
        document.addEventListener('livewire:updated', function() {
            setupDateDisabling();
        });
        

    </script>
    <form class="fi-form grid gap-y-6" enctype="multipart/form-data">
        {{ $this->form }}

        <div class="fi-form-actions">
            <div class="fi-ac gap-3 flex flex-wrap items-center justify-center">
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
                <div class="text-xl">Confirm Meal Plan</div>
            </x-slot>

            @include('filament.pages.meal-plan.partials.confirm-modal')

            <x-slot name="footerActions">
                <div class="w-full gap-3 flex flex-wrap items-center justify-center">
                    <x-filament::button
                        x-on:click="
                            () => {
                                $wire.save();
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
