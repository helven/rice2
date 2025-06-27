<?php

namespace App\Filament\Resources\MealResource\Pages;

use App\Filament\Resources\MealResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeal extends EditRecord
{
    protected static string $resource = MealResource::class;

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

    public function getBreadcrumbs(): array
    {
        return [
            '/'.config('filament.path', 'backend').'/meals' => 'Meals',
            '' => $this->record->name ?? 'Edit Meal',
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->label('Save Changes'),
            $this->getCancelFormAction()->label('Cancel'),
        ];
    }
}