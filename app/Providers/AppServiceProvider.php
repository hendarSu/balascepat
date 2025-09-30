<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure Livewire script route works regardless of APP_DEBUG and cache alignment
        try {
            if (class_exists(Livewire::class)) {
                Livewire::setScriptRoute(function ($handle) {
                    // Register both endpoints so either URL resolves
                    Route::get('/livewire/livewire.js', $handle);
                    Route::get('/livewire/livewire.min.js', $handle);

                    // Return the primary route for internal references
                    return Route::get('/livewire/livewire.js', $handle);
                });
            }
        } catch (\Throwable $e) {
            // No-op: avoid breaking boot if Livewire is not installed
        }
    }
}
