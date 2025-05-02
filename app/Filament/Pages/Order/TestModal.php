<?php

namespace App\Filament\Pages\Order;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;

class TestModal extends Page
{
    use InteractsWithForms;
    
    public bool $showTestModal = false;
    
    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $title = 'Test Modal';
    protected static ?string $slug = 'test-modal';
    protected static bool $shouldRegisterNavigation = false;
    
    protected static string $view = 'filament.pages.order.test-modal';

    public function openTestModal(): void
    {
        
    }
}