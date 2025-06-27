<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDriver extends EditRecord
{
    protected static string $resource = DriverResource::class;

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
                    $record->status_id = 11;
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
            '/'.config('filament.path', 'backend').'/drivers' => 'Drivers',
            '' => $record->name ?? 'Edit Driver',
        ];
    }
}
