<?php

namespace App\Providers;

use App\Repositories\CarRepository;
use App\Repositories\Eloquent\EloquentCarRepository;
use App\Repositories\Eloquent\EloquentInspectionRepository;
use App\Repositories\InspectionRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repository interfaces to their Eloquent implementations
        $this->app->bind(CarRepository::class, EloquentCarRepository::class);
        $this->app->bind(InspectionRepository::class, EloquentInspectionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
