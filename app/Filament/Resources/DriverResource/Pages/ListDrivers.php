<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDrivers extends ListRecords
{
    protected static string $resource = DriverResource::class;
    protected static ?string $navigationLabel = 'Drivers List';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Driver')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/backend/drivers' => 'Drivers',
            '' => 'Manage Drivers',
        ];
    }
}
