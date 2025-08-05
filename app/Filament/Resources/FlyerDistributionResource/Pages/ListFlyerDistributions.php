<?php

namespace App\Filament\Resources\FlyerDistributionResource\Pages;

use App\Filament\Resources\FlyerDistributionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFlyerDistributions extends ListRecords
{
    protected static string $resource = FlyerDistributionResource::class;
    protected static ?string $navigationLabel = 'Flyer Distributions List';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Flyer Distribution')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/'.config('filament.path', 'backend').'/flyer-distributions' => 'Flyer Distributions',
            '' => 'Manage Flyer Distributions',
        ];
    }
}