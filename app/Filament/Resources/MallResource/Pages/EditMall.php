<?php

namespace App\Filament\Resources\MallResource\Pages;

use App\Filament\Resources\MallResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMall extends EditRecord
{
    protected static string $resource = MallResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('delete')
                ->label('Delete')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->getRecord();
                    $record->status_id = 99;
                    $record->save();
                    
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->label('Save'),
            $this->getCancelFormAction()->label('Cancel'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        $record = $this->getRecord();
        return [
            '/'.config('filament.path', 'backend').'/malls' => 'Malls',
            '' => $record->name ?? 'Edit Mall',
        ];
    }
}