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
            '/backend/drivers' => 'Drivers',
            '' => 'New Driver',
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Save'),
            $this->getCreateAnotherFormAction()->label('Save & Create another'),
            $this->getCancelFormAction()->label('Cancel'),
        ];
    }
}
