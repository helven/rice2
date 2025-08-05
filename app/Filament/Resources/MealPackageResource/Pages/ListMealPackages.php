<?php

namespace App\Filament\Resources\MealPackageResource\Pages;

use App\Filament\Resources\MealPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMealPackages extends ListRecords
{
    protected static string $resource = MealPackageResource::class;
    protected static ?string $navigationLabel = 'Meal Packages List';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Meal Package')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/'.config('filament.path', 'backend').'/meal-packages' => 'Meal Packages',
            '' => 'Manage Meal Packages',
        ];
    }
}