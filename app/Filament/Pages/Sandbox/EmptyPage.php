<?php

namespace App\Filament\Pages\Sandbox;

use App\Filament\Pages\AbstractEmptyPage;

class EmptyPage extends AbstractEmptyPage
{
    protected static ?string $navigationGroup = 'Sandbox';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Empty Sandbox';
    protected static ?string $title = 'Empty Sandbox';
    protected static ?string $slug = 'sandbox/empty-page';
    protected static ?int $navigationSort = 3;
    
    protected static string $view = 'filament.pages.sandbox.empty-page';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}