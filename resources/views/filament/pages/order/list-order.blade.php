<x-filament::page>
    <style>
        td.fi-table-cell-payment-status\.label,
        td.fi-table-cell-invoice\.invoice-no {
            background-image: linear-gradient(rgba(230, 150, 0, 0.1), rgba(230, 150, 0, 0.1));
            
        }
        td.fi-table-cell-payment-status\.label:hover,
        td.fi-table-cell-invoice\.invoice-no:hover
            text-decoration: underline;
        }
    </style>
    {{ $this->table }}
</x-filament::page>