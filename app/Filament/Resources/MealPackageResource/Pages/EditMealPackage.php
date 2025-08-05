<?php

namespace App\Filament\Resources\MealPackageResource\Pages;

use App\Filament\Resources\MealPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMealPackage extends EditRecord
{
    protected static string $resource = MealPackageResource::class;
    protected static ?string $navigationLabel = 'Edit Meal Package';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/'.config('filament.path', 'backend').'/meal-packages' => 'Meal Packages',
            '/'.config('filament.path', 'backend').'/meal-packages' => 'Manage Meal Packages',
            '' => 'Edit Meal Package',
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->label('Update'),
            $this->getCancelFormAction()->label('Cancel'),
        ];
    }
}