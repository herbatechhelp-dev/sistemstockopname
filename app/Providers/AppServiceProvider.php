<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\SoEntry;
use App\Observers\SoEntryObserver;

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
        SoEntry::observe(SoEntryObserver::class);
    }
}
