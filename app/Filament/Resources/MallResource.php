<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MallResource\Pages;
use App\Filament\Resources\MallResource\RelationManagers;
use App\Models\Mall;
use App\Models\MallStatus;
use App\Models\AttrPaymentMethod;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Navigation\NavigationItem;

class MallResource extends Resource
{
    protected static ?string $navigationGroup = 'Malls';
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Manage Malls';

    public static function getNavigationItems(): array
    {
        return [
            ...parent::getNavigationItems(),
            NavigationItem::make('Manage Malls')
                ->icon('heroicon-o-building-storefront')
                ->group(static::getNavigationGroup())
                ->url(static::getUrl('index')),
            NavigationItem::make('New Mall')
                ->icon('heroicon-o-plus')
                ->group(static::getNavigationGroup())
                ->url(static::getUrl('create')),
        ];
    }

    protected static ?string $model = Mall::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Mall Information')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Mall Name'),
                                Select::make('status_id')
                                    ->label('Status')
                                    ->options(MallStatus::pluck('label', 'id'))
                                    ->default(1)
                                    ->required(),
                            ])
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Mall Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('paymentMethod.label')
                    ->label('Payment Method')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status.label')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->visibleFrom('lg'),
                TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('lg'),
            ])
            ->filters([
                SelectFilter::make('status_id')
                    ->label('Status')
                    ->options(MallStatus::pluck('label', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(function (Mall $record) {
                        $record->update(['status_id' => 99]);
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('delete')
                        ->label('Delete selected')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['status_id' => 99]);
                            }
                        })
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMalls::route('/'),
            'create' => Pages\CreateMall::route('/create'),
            'edit' => Pages\EditMall::route('/{record}/edit'),
        ];
    }
}