<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MealPackageResource\Pages;
use App\Models\MealPackage;
use App\Models\Meal;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Navigation\NavigationItem;

class MealPackageResource extends Resource
{
    protected static ?string $navigationGroup = 'Meals';
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Manage Meal Packages';

    public static function getNavigationItems(): array
    {
        return [
            ...parent::getNavigationItems(),
            NavigationItem::make('Manage Meal Packages')
                ->icon('heroicon-o-cube')
                ->group(static::getNavigationGroup())
                ->sort(3)
                ->url(static::getUrl('index')),
            NavigationItem::make('New Meal Package')
                ->icon('heroicon-o-plus')
                ->group(static::getNavigationGroup())
                ->sort(4)
                ->url(static::getUrl('create')),
        ];
    }

    protected static ?string $model = MealPackage::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Meal Package Information')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('meal_id')
                                    ->label('Meal')
                                    ->options(Meal::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Package Name'),
                            ]),
                        FileUpload::make('main_image')
                            ->label('Main Image')
                            ->image()
                            ->directory('meal-packages/main')
                            ->visibility('public')
                            ->columnSpanFull(),
                    ]),
                Section::make('Dish Images')
                    ->collapsible()
                    ->schema([
                        Repeater::make('dish_images')
                            ->label('Dish Images')
                            ->schema([
                                TextInput::make('dish_name')
                                    ->label('Dish Name')
                                    ->required()
                                    ->maxLength(255),
                                FileUpload::make('image')
                                    ->label('Dish Image')
                                    ->image()
                                    ->directory('meal-packages/dishes')
                                    ->visibility('public')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add Dish')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('meal.name')
                    ->label('Meal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Package Name')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('main_image')
                    ->label('Main Image')
                    ->size(60),
                TextColumn::make('dish_images')
                    ->label('Dish Images Count')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0)
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
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListMealPackages::route('/'),
            'create' => Pages\CreateMealPackage::route('/create'),
            'edit' => Pages\EditMealPackage::route('/{record}/edit'),
        ];
    }
}