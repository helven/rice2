<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    //return view('welcome');
    return redirect('/'.config('filament.path', 'backend'));
});
Route::get('/ok-banana-rice', function () {
    $key = request()->query('key');
    $validKey = env('CACHE_TRIGGER_KEY');

    if ($key !== $validKey) {
        abort(403, 'Unauthorized');
    }

    // Clear existing caches
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('event:clear');

    // Rebuild caches
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('event:cache');

    return response()->json([
        'status' => 'success',
        'message' => 'All caches cleared and rebuilt successfully.',
    ]);
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Subdomain routes
Route::domain('admin.' . str_replace(['http://', 'https://', 'www.'], '', config('app.url')))->group(function () {
    Route::get('/', function () {
        return 'Welcome to admin subdomain';
    });
    
    // Add more subdomain-specific routes here
    Route::get('/about', function () {
        return 'About page for admin subdomain';
    });
});

// Subdomain routes
Route::domain('api.' . str_replace(['http://', 'https://', 'www.'], '', config('app.url')))->group(function () {
    Route::get('/', function () {
        return 'Welcome to api subdomain';
    });
    
    // Add more subdomain-specific routes here
    Route::get('/about', function () {
        return 'About page for api subdomain';
    });
});
