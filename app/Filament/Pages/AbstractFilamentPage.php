<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;

class AbstractFilamentPage extends Page
{
    public function mount(): void
    {
        // Pass the title to the layout
        view()->share('title', static::$title.' - ' . config('app.name'));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}