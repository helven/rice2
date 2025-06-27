<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
    protected static ?string $navigationLabel = 'New Customer';
    protected static ?string $title = 'New Customer';

    public function getBreadcrumbs(): array
    {
        return [
            '/backend/customers' => 'Customers',
            '' => 'New Customer',
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