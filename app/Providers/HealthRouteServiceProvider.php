<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class HealthRouteServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(function () {
                Route::get('/health', \App\Http\Controllers\HealthController::class);
            });
    }
}
