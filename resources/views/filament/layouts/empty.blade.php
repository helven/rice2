<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        <style>[x-cloak] { display: none !important; }</style>

        @filamentStyles
    </head>

    <body class="antialiased filament-body bg-gray-100">
        <div class="filament-app-layout min-h-screen">
            <!-- Main content -->
            <main class="filament-main py-6">
                <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @filamentScripts
    </body>
</html>