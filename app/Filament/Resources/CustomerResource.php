<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Navigation\NavigationItem;

use App\Models\Area;
use App\Models\AttrPaymentMethod;
use App\Models\AttrStatus;
use App\Models\AttrState;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Mall;

class CustomerResource extends Resource
{
    protected static ?string $navigationGroup = 'Customers';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Manage Customers';

    public static function getNavigationItems(): array
    {
        return [
            ...parent::getNavigationItems(),
            NavigationItem::make('Manage Customers')
                ->icon('heroicon-o-users')
                ->group(static::getNavigationGroup())
                ->url(static::getUrl('index')),
            NavigationItem::make('New Customer')
                ->icon('heroicon-o-plus')
                ->group(static::getNavigationGroup())
                ->url(static::getUrl('create')),
        ];
    }

    protected static ?string $model = Customer::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Customer Information')
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
                                Select::make('payment_method_id')
                                    ->label('Payment Method')
                                    ->options(AttrPaymentMethod::query()->pluck('label', 'id'))
                                    ->required()
                                    ->searchable()
                            ]),
                    ]),
                Section::make('Address Information')
                    ->collapsible()
                    ->schema([
                        Repeater::make('addressBooks')
                            ->label('Addresses')
                            ->relationship()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Company Name')
                                    ->required()
                                    ->maxLength(64),
                                Grid::make(2)
                                    ->schema([
                                        Select::make('mall_id')
                                            ->label('Mall')
                                            ->placeholder('Select Mall')
                                            ->searchable()
                                            ->preload()
                                            ->options(Mall::query()->pluck('name', 'id'))
                                            ->nullable(),
                                        Select::make('area_id')
                                            ->label('Area')
                                            ->placeholder('Select Area')
                                            ->searchable()
                                            ->preload()
                                            ->options(Area::query()->pluck('name', 'id'))
                                            ->required(),
                                    ]),
                                TextInput::make('address_1')
                                    ->label('Address Line 1')
                                    ->required()
                                    ->maxLength(128),
                                TextInput::make('address_2')
                                    ->label('Address Line 2')
                                    ->maxLength(128)
                                    ->required(false)
                                    ->default('')
                                    ->dehydrateStateUsing(fn ($state) => $state ?? ''),
                                //Grid::make(3)
                                //    ->schema([
                                //        TextInput::make('postal_code')
                                //            ->label('Postal Code')
                                //            ->required()
                                //            ->maxLength(10),
                                //        TextInput::make('city')
                                //            ->required()
                                //            ->maxLength(64),
                                //        Select::make('state_id')
                                //            ->label('State')
                                //            ->options(AttrState::query()->pluck('label', 'id'))
                                //            ->required()
                                //            ->searchable(),
                                //    ]),

                                Grid::make(2)
                                    ->schema([
                                        Select::make('driver_id')
                                            ->label('Driver')
                                            ->placeholder('Select Driver')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->options(Driver::query()->pluck('name', 'id'))
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $set('driver_route', null);
                                            }),
                                        Select::make('driver_route')
                                            ->label('Route')
                                            ->placeholder('Select Route')
                                            ->required()
                                            ->searchable()
                                            ->options(function (callable $get) {
                                                $driverId = $get('driver_id');

                                                if (blank($driverId)) {
                                                    return [];
                                                }

                                                $driver = \App\Models\Driver::find($driverId);
                                                if (!$driver || !$driver->route) {
                                                    return [];
                                                }
                                                return collect($driver->route)->pluck('route_name', 'route_name');
                                            })
                                            ->disabled(fn(callable $get): bool => blank($get('driver_id')))
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        Select::make('backup_driver_id')
                                            ->label('Backup Driver')
                                            ->placeholder('Select Backup Driver')
                                            ->searchable()
                                            ->preload()
                                            ->options(Driver::query()->pluck('name', 'id'))
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $set('backup_driver_route', null);
                                            }),
                                        Select::make('backup_driver_route')
                                            ->label('Route')
                                            ->placeholder('Select Route')
                                            ->searchable()
                                            ->options(function (callable $get) {
                                                $driverId = $get('backup_driver_id');

                                                if (blank($driverId)) {
                                                    return [];
                                                }

                                                $driver = \App\Models\Driver::find($driverId);
                                                if (!$driver || !$driver->route) {
                                                    return [];
                                                }
                                                return collect($driver->route)->pluck('route_name', 'route_name');
                                            })
                                            ->disabled(fn(callable $get): bool => blank($get('driver_id')))
                                    ]),
                            ])
                            ->addActionLabel('Add Address')
                            ->columnSpanFull()
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('status_id', [1, 2]))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact')
                    ->label('Contact No')
                    ->searchable(),
                TextColumn::make('status.label')
                    ->label('Status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(function (Customer $record): string {
                        if ($record->status_id === 1) return 'success';
                        if ($record->status_id === 2) return 'warning';
                        return 'gray';
                    }),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(true),
                TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(false),
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
                    ->action(function (Customer $record) {
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
                            $records->each(function ($record) {
                                $record->update(['status_id' => 99]);
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}