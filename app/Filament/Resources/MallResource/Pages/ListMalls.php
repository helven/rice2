<?php

namespace App\Filament\Resources\MallResource\Pages;

use App\Filament\Resources\MallResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMalls extends ListRecords
{
    protected static string $resource = MallResource::class;
    protected static ?string $navigationLabel = 'Malls List';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Mall')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/'.config('filament.path', 'backend').'/malls' => 'Malls',
            '' => 'Manage Malls',
        ];
    }
}