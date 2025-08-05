<?php

namespace App\Filament\Resources\FlyerDistributionResource\Pages;

use App\Filament\Resources\FlyerDistributionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFlyerDistribution extends CreateRecord
{
    protected static string $resource = FlyerDistributionResource::class;
    protected static ?string $navigationLabel = 'New Flyer Distribution';
    protected static ?string $title = 'New Flyer Distribution';

    public function getBreadcrumbs(): array
    {
        return [
            '/'.config('filament.path', 'backend').'/flyer-distributions' => 'Flyer Distributions',
            '' => 'New Flyer Distribution',
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