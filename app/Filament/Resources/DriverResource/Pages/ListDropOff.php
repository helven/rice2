<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Actions\Action;

class ListDropOff extends ListRecords
{
    protected static string $resource = DriverResource::class;
    protected static ?string $navigationLabel = 'Drop Off';
    protected static ?string $title = 'Drop Off';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->recordUrl(fn (Order $record): string => "/backend/orders/{$record->id}/edit")
            ->headerActions([
                Action::make('printDropOff')
                    ->label('Print Drop Off')
                    ->button()
                    ->icon('heroicon-o-printer')
                    ->url(function () {
                        $params = [];
                        
                        $todaysDropoffFilter = $this->getTableFilterState('todays_dropoff');
                        if ($todaysDropoffFilter && isset($todaysDropoffFilter['isActive']) && $todaysDropoffFilter['isActive']) {
                            $params['date'] = now()->format('Y-m-d');
                        }
                        
                        $statusFilter = $this->getTableFilterState('status_id');
                        if ($statusFilter && isset($statusFilter['value']) && $statusFilter['value']) {
                            $params['status_id'] = $statusFilter['value'];
                        }
                        
                        $customerFilter = $this->getTableFilterState('customer');
                        if ($customerFilter && isset($customerFilter['value']) && $customerFilter['value']) {
                            $params['customer'] = $customerFilter['value'];
                        }
                        
                        $driverFilter = $this->getTableFilterState('driver');
                        if ($driverFilter && isset($driverFilter['value']) && $driverFilter['value']) {
                            $params['driver'] = $driverFilter['value'];
                        }
                        
                        return route('admin.order.print_dropoff', $params);
                    }, true),
            ])
            ->columns([
                TextColumn::make('formatted_id')
                    ->label('Order No')
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label('Delivery Date')
                    ->dateTime('Y-m-d')
                    ->sortable(),
                TextColumn::make('dropoff_time')
                    ->label('DropOff Time')
                    ->state(function ($record) {
                        return $record->dropoff_time ?: 'NULL';
                    })
                    ->formatStateUsing(function ($state): string {
                        if ($state === 'NULL') {
                            return 'Set dropoff time';
                        }
                        return \Carbon\Carbon::parse($state)->format('h:i A');
                    })
                    ->icon('heroicon-m-pencil-square')
                    ->iconPosition('before')
                    ->html()
                    ->sortable()
                    ->action(
                        Action::make('editDropoffTimeInline')
                            ->label('Edit Dropoff Time')
                            ->modalHeading('Edit Dropoff Time')
                            ->modalWidth('sm')
                            ->form([
                                TimePicker::make('dropoff_time')
                                    ->label('Dropoff Time')
                                    ->required()
                                    ->seconds(false), // Don't include seconds
                            ])
                            ->action(function (Order $record, array $data): void {
                                $record->update(['dropoff_time' => $data['dropoff_time']]);
                            })
                            ->fillForm(fn (Order $record): array => [
                                'dropoff_time' => $record->dropoff_time,
                            ])
                    ),
                TextColumn::make('arrival_time')
                    ->label('Arrival Time')
                    ->dateTime('H:i A')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address.name')
                    ->label('Delivery Location')
                    ->formatStateUsing(function ($record) {
                        if (!$record->address) {
                            return '';
                        }
                        return new HtmlString(
                            '<span class="font-bold">' . e($record->address->name) . '</span><br />' .
                            e($record->address->address_1).', '.e($record->address->city)
                        );
                    })
                    ->html(),
                TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable()
                    ->toggleable(true),
            ])
            ->filters([
                Tables\Filters\Filter::make('todays_dropoff')
                    ->label("Today's Drop Off")
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereDate('delivery_date', now())),
                SelectFilter::make('status_id')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        2 => 'Inactive',
                    ]),
                SelectFilter::make('customer')
                    ->relationship('customer', 'name'),
                SelectFilter::make('driver')
                    ->relationship('driver', 'name'),
            ])
            ->defaultSort('delivery_date', 'desc');
            // Removed separate actions column as requested
    }

    protected function query(): Builder
    {
        return Order::query()->whereIn('status_id', [1, 2]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/'.config('filament.path', 'backend').'/drivers' => 'Drivers',
            '' => 'Drop Off',
        ];
    }
}
