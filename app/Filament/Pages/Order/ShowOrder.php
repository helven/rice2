<?php

namespace App\Filament\Pages\Order;

use Filament\Pages\Page;
use App\Models\Order;

class ShowOrder extends Page
{
    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $title = 'View Order';
    protected static ?string $slug = 'orders/{id}';
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.order.show-order';

    public ?Order $order = null;

    public function mount($id): void
    {
        $this->order = Order::with([
            'customer',
            'payment_status',
            'payment_method',
            'meals.meal',
            'deliveries.driver',
            'deliveries.backupDriver',
            'deliveries.address',
            'deliveries.status',
            'invoices.status'
        ])->findOrFail($id);
    }

    public function getOrderData(): array
    {
        $firstDelivery = $this->order->deliveries->first();
        $address = $firstDelivery?->address;
        
        $deliveryLocation = '';
        if ($address) {
            $deliveryLocation = $address->name . ', ' . $address->address_1;
            if ($address->address_2) {
                $deliveryLocation .= ', ' . $address->address_2;
            }
            $deliveryLocation .= ', ' . $address->postcode . ' ' . $address->city;
        }
        
        return [
            'order_no' => $this->order->order_no,
            'customer_name' => $this->order->customer->name ?? '',
            'delivery_location' => $deliveryLocation,
            'payment_status' => $this->order->payment_status->label ?? '',
            'payment_method' => $this->order->payment_method->label ?? '',
            'delivery_date' => $firstDelivery?->delivery_date?->format(config('app.date_format')) ?? '',
        ];
    }

    public function getMealsData(): array
    {
        return $this->order->meals->map(function ($meal) {
            return [
                'meal_name' => $meal->meal->name ?? '',
                'normal' => $meal->normal,
                'big' => $meal->big,
                'small' => $meal->small,
                'no_rice' => $meal->no_rice,
            ];
        })->toArray();
    }

    public function getDriverData(): array
    {
        $firstDelivery = $this->order->deliveries->first();
        
        return [
            'arrival_time' => $firstDelivery?->arrival_time ? date('h:i A', strtotime($firstDelivery->arrival_time)) : '-',
            'dropoff_time' => $firstDelivery?->dropoff_time ? date('h:i A', strtotime($firstDelivery->dropoff_time)) : '-',
            'driver' => $firstDelivery?->driver?->name ?? '-',
            'route' => $firstDelivery?->driver_route ?? '-',
            'backup_driver' => $firstDelivery?->backupDriver?->name ?? '-',
            'notes' => $firstDelivery?->driver_notes ?? '',
        ];
    }

    public function getInvoicesData(): array
    {
        return $this->order->invoices
            ->sortBy(function ($invoice) {
                return $invoice->status_id == 1 ? 0 : 1;
            })
            ->sortBy('issue_date', SORT_REGULAR, true)
            ->map(function ($invoice) {
                return [
                    'issue_date' => $invoice->issue_date->format(config('app.date_format')),
                    'status' => $invoice->status->label ?? '',
                    'is_active' => $invoice->status_id == 1,
                    'invoice_no' => $invoice->invoice_no,
                    'subtotal' => number_format($invoice->subtotal, 2),
                    'delivery_fee' => number_format($invoice->delivery_fee, 2),
                    'tax_rate' => number_format($invoice->tax_rate, 2),
                    'total_amount' => number_format($invoice->total_amount, 2),
                ];
            })->toArray();
    }

    public function getDeliveryData(): array
    {
        $delivery = $this->order->deliveries->first();
        $today = now()->format('Y-m-d');
        
        if (!$delivery) {
            return [
                'delivery_no' => '-',
                'delivery_date' => '-',
                'arrival_time' => '-',
                'dropoff_time' => '-',
                'status' => '-',
                'status_id' => null,
                'is_today' => false,
            ];
        }
        
        return [
            'delivery_no' => $delivery->delivery_no,
            'delivery_date' => $delivery->delivery_date->format(config('app.date_format')),
            'arrival_time' => $delivery->arrival_time ? date('h:i A', strtotime($delivery->arrival_time)) : '-',
            'dropoff_time' => $delivery->dropoff_time ? date('h:i A', strtotime($delivery->dropoff_time)) : '-',
            'status' => $delivery->status->label ?? '',
            'status_id' => $delivery->status_id,
            'is_today' => $delivery->delivery_date->format('Y-m-d') === $today,
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('edit')
                ->label('Edit Order')
                ->url(EditOrder::getUrl(['id' => $this->order->id]))
                ->color('primary'),
        ];
    }
}
