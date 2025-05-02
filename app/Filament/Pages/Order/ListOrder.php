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
                TextColumn::make('order_no')
                    ->label('Order No')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label('Delivery Date')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('MYR')
                    ->sortable(),
                TextColumn::make('status.label')
                    ->label('Status')
                    ->badge()
                    ->color(function (Order $record): string {
                        if ($record->status_id === 1) return 'success';
                        if ($record->status_id === 2) return 'warning';
                        return 'gray';
                    }),
                TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Ordered Date')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('status_id')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        2 => 'Inactive',
                    ]),
                SelectFilter::make('driver')
                    ->relationship('driver', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (Order $record): string => "/admin/orders/{$record->id}/edit"),
                Tables\Actions\DeleteAction::make()
                    ->action(function ($record) {
                        $record->update(['status_id' => 11]);
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
                                $record->update(['status_id' => 11]);
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
                ->url('/admin/orders/create')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/admin/orders' => 'Orders',
            '' => 'Manage Orders',
        ];
    }
}
