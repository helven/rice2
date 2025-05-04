<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    //return view('welcome');
    return redirect('/backend');
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
