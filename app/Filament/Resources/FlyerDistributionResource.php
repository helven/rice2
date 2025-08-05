<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlyerDistributionResource\Pages;
use App\Models\FlyerDistribution;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Navigation\NavigationItem;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;

class FlyerDistributionResource extends Resource
{
    protected static ?string $navigationGroup = 'Flyer Distributions';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Manage Flyer Distributions';

    public static function getNavigationItems(): array
    {
        return [
            ...parent::getNavigationItems(),
            NavigationItem::make('Manage Flyer Distributions')
                ->icon('heroicon-o-document-text')
                ->group(static::getNavigationGroup())
                ->url(static::getUrl('index')),
            NavigationItem::make('New Flyer Distribution')
                ->icon('heroicon-o-plus')
                ->group(static::getNavigationGroup())
                ->url(static::getUrl('create')),
        ];
    }

    protected static ?string $model = FlyerDistribution::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Flyer Distribution Information')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Flatpickr::make('date')
                                    ->label('Distribution Date')
                                    ->format(config('app.date_format'))
                                    ->displayFormat(config('app.date_format'))
                                    ->default(now()->format(config('app.date_format')))
                                    ->required(),
                                TextInput::make('location')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Location')
                                    ->required(),
                            ]),
                        TextInput::make('area')
                            ->required()
                            ->maxLength(255)
                            ->label('Area')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date('Y-m-d')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('area')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime(config('app.datetime_format'))
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->visibleFrom('lg'),
                TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime(config('app.datetime_format'))
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('lg'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->striped();
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
            'index' => Pages\ListFlyerDistributions::route('/'),
            'create' => Pages\CreateFlyerDistribution::route('/create'),
            'edit' => Pages\EditFlyerDistribution::route('/{record}/edit'),
        ];
    }
}