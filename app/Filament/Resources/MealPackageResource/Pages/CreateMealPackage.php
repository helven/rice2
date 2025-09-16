<?php

namespace App\Filament\Resources\MealPackageResource\Pages;

use App\Filament\Resources\MealPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMealPackage extends CreateRecord
{
    protected static string $resource = MealPackageResource::class;
    protected static ?string $navigationLabel = 'New Meal Package';
    protected static ?string $title = 'New Meal Package';

    public function getBreadcrumbs(): array
    {
        return [
            '/'.config('filament.path', 'backend').'/meal-packages' => 'Meal Packages',
            '' => 'New Meal Package',
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