<?php

namespace App\Filament\Resources\MealPackageResource\Pages;

use App\Filament\Resources\MealPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

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

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        if (filled($search = $this->getTableSearch())) {
            $searchLower = strtolower($search);
            $mysqlVersion = \DB::select('SELECT VERSION() as version')[0]->version;
            $supportsJson = version_compare($mysqlVersion, '5.7.0', '>=');

            if ($supportsJson) {
                $query->where(function (Builder $subQuery) use ($searchLower) {
                    $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                        ->orWhereRaw("JSON_SEARCH(LOWER(dish_images), 'all', ?) IS NOT NULL", ["%{$searchLower}%"]);
                });
            } else {
                $query->where(function (Builder $subQuery) use ($searchLower) {
                    $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                        ->orWhereRaw('LOWER(dish_images) LIKE ?', ["%{$searchLower}%"]);
                });
            }
        }

        return $query;
    }
}
