<?php

namespace App\Filament\Pages\Order;

use Filament\Pages\Page;

class ListOrder extends Page
{
    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Manage Orders';
    protected static ?string $title = 'Manage Orders';
    protected static ?string $slug = 'orders';
    protected static ?int $navigationSort = 1;
    
    protected static string $view = 'filament.pages.order.list-order';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
    