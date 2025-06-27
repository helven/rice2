<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;
    protected static ?string $navigationLabel = 'Customers List';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Customer')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/'.config('filament.path', 'backend').'/customers' => 'Customers',
            '' => 'Manage Customers',
        ];
    }
}