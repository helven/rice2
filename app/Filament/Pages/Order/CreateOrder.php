<?php

namespace App\Filament\Pages\Order;

use Filament\Pages\Page;

class CreateOrder extends Page
{
    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $navigationIcon = 'heroicon-o-plus';
    protected static ?string $navigationLabel = 'Create Order';
    protected static ?string $title = 'Create New Order';
    protected static ?string $slug = 'orders/create';
    protected static ?int $navigationSort = 2;
    
    protected static string $view = 'filament.pages.order.create-order';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
    