<?php

namespace App\Filament\Resources\MealResource\Pages;

use App\Filament\Resources\MealResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMeal extends CreateRecord
{
    protected static string $resource = MealResource::class;
    protected static ?string $title = 'New Meal';

    public function getBreadcrumbs(): array
    {
        return [
            '/backend/meals' => 'Meals',
            '' => 'New Meal',
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