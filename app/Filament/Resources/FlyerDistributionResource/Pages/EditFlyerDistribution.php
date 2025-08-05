<?php

namespace App\Filament\Resources\FlyerDistributionResource\Pages;

use App\Filament\Resources\FlyerDistributionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFlyerDistribution extends EditRecord
{
    protected static string $resource = FlyerDistributionResource::class;
    protected static ?string $navigationLabel = 'Edit Flyer Distribution';
    protected static ?string $title = 'Edit Flyer Distribution';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/'.config('filament.path', 'backend').'/flyer-distributions' => 'Flyer Distributions',
            '' => 'Edit Flyer Distribution',
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->label('Save'),
            $this->getCancelFormAction()->label('Cancel'),
        ];
    }
}