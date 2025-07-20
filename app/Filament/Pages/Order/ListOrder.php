<?php

namespace App\Filament\Pages\Order;

use App\Models\Order;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class ListOrder extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Manage Orders';
    protected static ?string $title = 'Manage Orders';
    protected static ?string $slug = 'orders';
    protected static bool $shouldRegisterNavigation = true;
    protected static ?int $navigationSort = 1;
    
    protected static string $view = 'filament.pages.order.list-order';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->columns([
                TextColumn::make('formatted_id')
                    ->label('Order No')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label('Delivery Date')
                    ->dateTime('d M Y')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->numeric(2, '.', ',')
                    ->prefix('RM ')
                    ->sortable(),
                TextColumn::make('payment_status.label')
                    ->label('Payment')
                    ->formatStateUsing(function (Order $record): string {
                        $status = $record->payment_status->label ?? '';
                        $method = $record->payment_method->label ?? '';
                        return $method ? "{$status} <span class='text-gray-950'>({$method})</span>" : $status;
                    })
                    ->html()
                    ->searchable()
                    ->sortable()
                    ->color(function (Order $record): string {
                        if ($record->payment_status_id === 4) return 'success';
                        if ($record->payment_status_id === 3) return 'warning';
                        return 'gray';
                    })
                    ->action(
                        TableAction::make('editPaymentStatus')
                            ->form([
                                Select::make('payment_status_id')
                                    ->label('Payment Status')
                                    ->native()
                                    ->selectablePlaceholder(false)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ((string)$state === 3) {
                                            $set('payment_method_id', null);
                                        }
                                    })
                                    ->options([
                                        3 => 'Unpaid',
                                        4 => 'Paid',
                                    ])
                                    ->default(function (Order $record): int {
                                        return $record->payment_status_id;
                                    }),
                                Select::make('payment_method_id')
                                    ->label('Payment Method')
                                    ->native()
                                    ->placeholder('Select Payment Method')
                                    ->relationship('payment_method', 'label')
                                    ->required(fn (callable $get): bool => (string)$get('payment_status_id') === 4)
                                    ->default(function (Order $record): ?int {
                                        return $record->payment_method_id;
                                    })
                                    ->disabled(fn (callable $get) => (string)$get('payment_status_id') === 3)
                            ])
                            ->action(function (Order $record, array $data): void {
                                $data['payment_method_id'] = (string)$data['payment_status_id'] === 3 ? null : $data['payment_method_id'];
                                $record->update([
                                    'payment_status_id' => $data['payment_status_id'],
                                    'payment_method_id' => $data['payment_method_id'],
                                ]);
                            })
                            ->icon('heroicon-m-pencil-square')
                    ),
                TextColumn::make('status.label')
                    ->label('Status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(function (Order $record): string {
                        if ($record->status_id === 1) return 'success';
                        if ($record->status_id === 2) return 'warning';
                        return 'gray';
                    })
                    ->toggleable(true),
                TextColumn::make('created_at')
                    ->label('Ordered On')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(true),
                TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(true)
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make('payment_status_id')
                    ->label('Payment')
                    ->options([
                        3 => 'Paid',
                        4 => 'Unpaid',
                    ]),
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
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (Order $record): string => "/backend/orders/{$record->id}/edit"),
                Tables\Actions\DeleteAction::make()
                    ->action(function ($record) {
                        $record->update(['status_id' => 99]);
                    }),
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
            ->defaultSort('delivery_date', 'desc');
    }

    protected function query(): Builder
    {
        return Order::query()->whereIn('status_id', [1, 2]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('New Order')
                ->url('/'.config('filament.path', 'backend').'/orders/create')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function get()
    {
        return [
            '/'.config('filament.path', 'backend').'/orders' => 'Orders',
            '' => 'Manage Orders',
        ];
    }
}
