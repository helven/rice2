<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Card;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;
use Carbon\Carbon;

class Report extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Reports';
    protected static ?string $title = 'Reports';
    protected static ?int $navigationSort = 10;
    
    protected static string $view = 'filament.pages.report';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    // Form properties
    public $daily_bank_sales_date;
    public $monthly_sales_month;
    public $daily_order_quantity_date;

    public function mount(): void
    {
        // Initialize form with current date/month
        $this->form->fill([
            'daily_bank_sales_date' => now()->format('Y-m-d'),
            'monthly_sales_month' => now()->format('Y-m'),
            'daily_order_quantity_date' => now()->format('Y-m-d'),
        ]);
    }



    protected function getFormSchema(): array
    {
        return [
            Grid::make(1)->schema([
                // Daily Bank Sales Report Section
                Section::make('Daily Bank Sales Report')
                    ->description('Generate daily bank sales report for a specific date')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('daily_bank_sales_date')
                                ->label('Select Date')
                                ->default(now())
                                ->live()
                                ->required()
                                ->columnSpan(1),
                            Actions::make([
                                Action::make('print_daily_bank_sales')
                                    ->label('Print Report')
                                    ->icon('heroicon-o-printer')
                                    ->url(fn () => route('admin.report.print_daily_bank_sales_report', [
                                        'date_range' => 'daily',
                                        'daily_date' => $this->form->getState()['daily_bank_sales_date'] ?? now()->format('Y-m-d')
                                    ]))
                                    ->openUrlInNewTab()
                                    ->extraAttributes([
                                        'wire:loading.attr' => 'disabled',
                                        'wire:loading.class' => 'pointer-events-none'
                                    ]),
                            ])->columnSpan(1)->alignEnd(),
                        ]),
                    ]),

                // Monthly Sales Report Section
                Section::make('Monthly Sales Report')
                    ->description('Generate monthly sales report for a specific month')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('monthly_sales_month')
                                ->label('Select Month')
                                ->format('Y-m')
                                ->default(now()->format('Y-m'))
                                ->native()
                                ->extraInputAttributes(['type' => 'month'])
                                ->live()
                                ->required()
                                ->columnSpan(1),
                            Actions::make([
                                Action::make('print_monthly_sales')
                                    ->label('Print Report')
                                    ->icon('heroicon-o-printer')
                                    ->url(fn () => route('admin.report.print_monthly_sales_report', [
                                        'date_range' => 'monthly',
                                        'month' => Carbon::parse(($this->form->getState()['monthly_sales_month'] ?? now()->format('Y-m')))->endOfMonth()->format('Y-m')
                                    ]))
                                    ->openUrlInNewTab()
                                    ->extraAttributes([
                                        'wire:loading.attr' => 'disabled',
                                        'wire:loading.class' => 'pointer-events-none'
                                    ]),
                            ])->columnSpan(1)->alignEnd(),
                        ]),
                    ]),

                // Daily Order Quantity Report Section
                Section::make('Daily Order Quantity Report')
                    ->description('Generate daily order quantity report for a specific date')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('daily_order_quantity_date')
                                ->label('Select Date')
                                ->default(now())
                                ->live()
                                ->required()
                                ->columnSpan(1),
                            Actions::make([
                                Action::make('print_daily_order_quantity')
                                    ->label('Print Report')
                                    ->icon('heroicon-o-printer')
                                    ->url(fn () => route('admin.report.print_daily_order_quantity_report', [
                                        'date_range' => 'daily',
                                        'daily_date' => $this->form->getState()['daily_order_quantity_date'] ?? now()->format('Y-m-d')
                                    ]))
                                    ->openUrlInNewTab()
                                    ->extraAttributes([
                                        'wire:loading.attr' => 'disabled',
                                        'wire:loading.class' => 'pointer-events-none'
                                    ]),
                            ])->columnSpan(1)->alignEnd(),
                        ]),
                    ]),
            ]),
        ];
    }
}