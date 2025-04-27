<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;

class AbstractEmptyPage extends Page
{
    protected static string $layout = 'filament.layouts.empty';

    public function mount(): void
    {
        // Register core Filament CSS
        FilamentAsset::register([
            Css::make('filament-core', asset('css/filament/filament/app.css'))
                ->loadedOnRequest(false)
        ]);

        // Pass the title to the layout
        view()->share('title', static::$title.' - ' . config('app.name'));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}