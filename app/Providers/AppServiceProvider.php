<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Asset;
use App\Observers\AssetObserver;

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
        // Daftarkan model asset agar dipantau oleh AssetObserver
        Asset::observe(AssetObserver::class);
    }
}