<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use App\Models\AttrStatus;
use App\Models\AttrState;
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
                                TextInput::make('address_1')
                                    ->label('Address Line 1')
                                    ->required()
                                    ->maxLength(128),
                                TextInput::make('address_2')
                                    ->label('Address Line 2')
                                    ->maxLength(128),
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('postal_code')
                                            ->label('Postal Code')
                                            ->required()
                                            ->maxLength(10),
                                        TextInput::make('city')
                                            ->required()
                                            ->maxLength(64),
                                        Select::make('state_id')
                                            ->label('State')
                                            ->options(AttrState::query()->pluck('label', 'id'))
                                            ->required()
                                            ->searchable(),
                                    ]),
                            ])
                            ->addActionLabel('Add Address')
                            ->defaultItems(1)
                            ->columnSpanFull()
                    ]),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}