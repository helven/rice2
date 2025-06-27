<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

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
            '/'.config('filament.path', 'backend').'/customers' => 'Customers',
            '' => $record->name ?? 'Edit Customer',
        ];
    }
}