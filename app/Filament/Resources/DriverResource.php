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

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group'; // Changed icon for relevance

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
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Added Delete action
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
