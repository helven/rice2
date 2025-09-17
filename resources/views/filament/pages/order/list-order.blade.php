<x-filament::page>
    <style>
        td.fi-table-cell-payment-status\.label,
        td.fi-table-cell-invoice\.invoice-no {
            background-image: linear-gradient(rgba(230, 150, 0, 0.1), rgba(230, 150, 0, 0.1));
            
        }
        td.fi-table-cell-payment-status\.label:hover,
        td.fi-table-cell-invoice\.invoice-no:hover {
            text-decoration: underline;
        }
    </style>
    
    {{ $this->table }}
    <h1 class="mt-6 fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
        Print Orders
    </h1>
    <!-- Print Report Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Report</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="dailyReportDate" class="block text-sm font-medium text-gray-700 mb-2">
                    Select Date
                </label>
                <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500">
                    <input 
                        type="date" 
                        id="dailyReportDate"
                        wire:model.live="dailyReportDate"
                        class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 bg-white/0 ps-3 pe-3"
                    />
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <x-filament::button 
                wire:click="printData" 
                icon="heroicon-o-printer" 
                color="primary" 
            > 
                Print Data 
            </x-filament::button>
            
            <x-filament::button 
                wire:click="PrintDailyBankSalesReport" 
                icon="heroicon-o-printer" 
                color="primary" 
            > 
                Print Daily Bank Sales Report 
            </x-filament::button>
            
            <x-filament::button 
                wire:click="PrintDailyOrderQuantityReport" 
                icon="heroicon-o-printer" 
                color="primary" 
            > 
                Print Daily Order Quantity Report 
            </x-filament::button>
        </div>
    </div>

    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Report</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="monthlyReportMonth" class="block text-sm font-medium text-gray-700 mb-2">
                    Select Month
                </label>
                <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500">
                    <input 
                        type="month" 
                        id="monthlyReportMonth"
                        wire:model.live="monthlyReportMonth"
                        class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 bg-white/0 ps-3 pe-3"
                    />
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <x-filament::button 
                wire:click="printMonthlySalesReport" 
                icon="heroicon-o-printer" 
                color="primary" 
            > 
                Print Monthly Sales Report 
            </x-filament::button>
        </div>
    </div>


    <!-- Print Driver Sheet Card -->
    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Driver Sheet</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="driverSheetDate" class="block text-sm font-medium text-gray-700 mb-2">
                    Select Date
                </label>
                <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500">
                    <input 
                        type="date" 
                        id="driverSheetDate"
                        wire:model.live="driverSheetDate"
                        class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 bg-white/0 ps-3 pe-3"
                    />
                </div>
            </div>
            <div>
                <label for="driverList" class="block text-sm font-medium text-gray-700 mb-2">
                    Select Driver
                </label>
                <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500">
                    <select 
                        id="driverList"
                        wire:model.live="driverList"
                        class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 bg-white/0 ps-3 pe-3"
                    >
                        <option value="">All Drivers</option>
                        @foreach($this->drivers as $driverId => $driverName)
                            <option value="{{ $driverId }}">{{ $driverName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <x-filament::button 
                wire:click="printDriverSheet1" 
                icon="heroicon-o-printer" 
                color="primary" 
            > 
                Print Driver Sheet 1 
            </x-filament::button>
            
            <x-filament::button 
                wire:click="printDriverSheet2" 
                icon="heroicon-o-printer" 
                color="primary" 
            > 
                Print Driver Sheet 2 
            </x-filament::button>
        </div>
    </div>
</x-filament::page>