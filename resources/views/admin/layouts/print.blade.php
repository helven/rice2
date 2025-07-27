<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="./">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="Doko">
        <meta name="keyword" content="">
        <title>@yield(__('title'))</title>
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon/favicon-192x192.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/favicon/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ asset('images/favicon/manifest.json') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('images/favicon/favicon-192x192.png') }}">
        <meta name="theme-color" content="#ffffff">
        <link rel="stylesheet" href="{{ asset('css/reset.css') }}?ver={{ filemtime(public_path('css/reset.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/print.css') }}?ver={{ filemtime(public_path('css/print.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/filament/custom.css') }}?ver={{ filemtime(public_path('css/filament/custom.css')) }}">
        <?php /*<link rel="stylesheet" href="{{ asset('css/app.css') }}?ver={{ filemtime(public_path('css/app.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/admin.css') }}?ver={{ filemtime(public_path('css/admin.css')) }}">*/ ?>
        @yield('head')
    </head>
    <body class="app">
        @yield('content')
        @yield('style')
        @yield('js')
    </body>
</html>