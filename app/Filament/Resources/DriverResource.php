<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Filament\Resources\DriverResource\RelationManagers;
use App\Models\Driver;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Navigation\NavigationItem;

class DriverResource extends Resource
{
    protected static ?string $navigationGroup = 'Drivers';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Manage Drivers';
    protected static ?int $navigationSort = 2;

    public static function getNavigationItems(): array
    {
        return [
            ...parent::getNavigationItems(),
            NavigationItem::make('Manage Drivers')
                ->icon('heroicon-o-truck')
                ->group(static::getNavigationGroup())
                ->sort(static::getNavigationSort() + 1)
                ->url(static::getUrl('index')),
            NavigationItem::make('New Driver')
                ->icon('heroicon-o-plus')
                ->group(static::getNavigationGroup())
                ->sort(static::getNavigationSort() + 1)
                ->url(static::getUrl('create')),
        ];
    }

    protected static ?string $model = Driver::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Driver Information')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(64),
                                TextInput::make('contact')
                                    ->label('Contact No')
                                    ->tel()
                                    ->required()
                                    ->maxLength(64),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('ic_name')
                                    ->label('IC Name')
                                    ->required()
                                    ->maxLength(64),
                                TextInput::make('ic_no')
                                    ->label('IC No')
                                    ->required()
                                    ->maxLength(64),
                            ]),
                        Textarea::make('address')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make('Route Information')
                    ->collapsible()
                    ->schema([
                        Repeater::make('route')
                            ->label('Routes')
                            ->schema([
                                TextInput::make('route_name')
                                    ->label('Route')
                                    ->required()
                                    ->default(function (Forms\Get $get) {
                                        $items = $get('../../route') ?? [];
                                        return sprintf('Route %d', count($items));
                                    }),
                            ])
                            ->addActionLabel('Add Route')
                            ->columns(1)
                            ->columnSpanFull()
                            ->defaultItems(1)
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status_id', [1, 2]))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact')
                    ->label('Contact No')
                    ->searchable(),
                TextColumn::make('ic_name')
                    ->label('IC Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('ic_no')
                    ->label('IC No')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('status.label')
                    ->label('Status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(function (Driver $record): string {
                        if ($record->status_id === 1) return 'success';
                        if ($record->status_id === 2) return 'warning';
                        return 'gray';
                    }),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('status_id')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        2 => 'Inactive'
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(function (Driver $record) {
                        $record->update(['status_id' => 11]);
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
                            $records->each(function ($record) {
                                $record->update(['status_id' => 11]);
                            });
                        })
                ]),
            ])
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
