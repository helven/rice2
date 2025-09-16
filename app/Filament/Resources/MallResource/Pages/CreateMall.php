<?php

namespace App\Filament\Resources\MallResource\Pages;

use App\Filament\Resources\MallResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMall extends CreateRecord
{
    protected static string $resource = MallResource::class;
    protected static ?string $navigationLabel = 'New Mall';
    protected static ?string $title = 'New Mall';

    public function getBreadcrumbs(): array
    {
        return [
            '/'.config('filament.path', 'backend').'/malls' => 'Malls',
            '' => 'New Mall',
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}