<?php

namespace App\Providers;

use App\Models\DataAntropometry;
use Illuminate\Support\ServiceProvider;
use App\Observers\DataAntropometryObserver;

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
        DataAntropometry::observe(DataAntropometryObserver::class);
    }
}
