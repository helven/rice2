<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDriver extends CreateRecord
{
    protected static string $resource = DriverResource::class;
    protected static ?string $navigationLabel = 'New Driver';
    protected static ?string $title = 'New Driver';

    public function getBreadcrumbs(): array
    {
        return [
            '/admin/drivers' => 'Drivers',
            '' => 'New Driver',
        ];
    }
}
