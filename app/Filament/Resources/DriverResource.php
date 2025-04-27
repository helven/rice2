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
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Navigation\NavigationItem;

class DriverResource extends Resource
{
    protected static ?string $navigationGroup = 'Drivers';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Manage Drivers';
    protected static ?int $navigationSort = 1;

    public static function getNavigationItems(): array
    {
        return [
            ...parent::getNavigationItems(),
            NavigationItem::make('Manage Drivers')
                ->icon('heroicon-o-truck')
                ->group(static::getNavigationGroup())
                ->sort(static::getNavigationSort() + 1)
                ->url(static::getUrl('index')),
            NavigationItem::make('Create Driver')
                ->icon('heroicon-o-plus')
                ->group(static::getNavigationGroup())
                ->sort(static::getNavigationSort() + 1)
                ->url(static::getUrl('create')),
            NavigationItem::make('Export Drivers')
                ->icon('heroicon-o-arrow-down-on-square-stack')
                ->group(static::getNavigationGroup())
                ->sort(static::getNavigationSort() + 2)
                ->url(static::getUrl('create')),
        ];
    }
    
    protected static ?string $model = Driver::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(32),
                        TextInput::make('contact')
                            ->label('Contact No')
                            ->tel()
                            ->required()
                            ->maxLength(32),
                    ]),
                Grid::make(2)
                    ->schema([
                        TextInput::make('ic_name')
                            ->label('IC Name')
                            ->required()
                            ->maxLength(32),
                        TextInput::make('ic_no')
                            ->label('IC No')
                            ->required()
                            ->maxLength(15)
                            ->mask('999999-99-9999')
                            ->regex('/^\d{6}-\d{2}-\d{4}$/')
                            ->placeholder('123456-78-9012')
                            ->helperText('Format: 123456-78-9012'),
                    ]),
                Textarea::make('address')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => 
                $query->where('status', '!=', '11')
                    ->with('attr_status')  // Eager load the relationship
            )
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('contact')
                    ->label('Contact No')
                    ->searchable(),
                TextColumn::make('ic_name')
                    ->label('IC Name')
                    ->searchable(),
                TextColumn::make('ic_no')
                    ->label('IC No')
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(true),
                TextColumn::make('attr_status.label')  // Use the relationship
                    ->label('Status'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(function (Driver $record): void {
                        $record->status = 11;
                        $record->save();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('delete')
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->status = 11;
                                $record->save();
                            });
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
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
