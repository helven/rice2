<x-filament::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Orders Overview Card -->
        <x-filament::card>
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold tracking-tight">Orders Overview</h2>
                <span class="text-sm text-gray-500">Today</span>
            </div>
            <div class="mt-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Total Orders</span>
                        <p class="text-2xl font-semibold">{{ \App\Models\Order::count() }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Today's Orders</span>
                        <p class="text-2xl font-semibold">{{ \App\Models\Order::whereDate('created_at', today())->count() }}</p>
                    </div>
                </div>
            </div>
        </x-filament::card>

        <!-- Delivery Overview Card -->
        <x-filament::card>
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold tracking-tight">Delivery Overview</h2>
                <span class="text-sm text-gray-500">Today</span>
            </div>
            <div class="mt-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Pending Deliveries</span>
                        <p class="text-2xl font-semibold">{{ \App\Models\Order::whereDate('delivery_date', today())->where('dropoff_time', '')->count() }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Completed Deliveries</span>
                        <p class="text-2xl font-semibold">{{ \App\Models\Order::whereDate('delivery_date', today())->where('dropoff_time', '!=', '')->count() }}</p>
                    </div>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament::page>