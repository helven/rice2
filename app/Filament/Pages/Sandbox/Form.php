<?php

namespace App\Filament\Pages\Sandbox;


use App\Filament\Pages\AbstractFilamentPage;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;

class Form extends AbstractFilamentPage
{
    protected static ?string $navigationGroup = 'Sandbox';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Form Sandbox';
    protected static ?string $title = 'Form Sandbox';
    protected static ?string $slug = 'sandbox/form';
    protected static ?int $navigationSort = 3;
    
    protected static string $view = 'filament.pages.sandbox.form';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
