<?php

namespace Vinhdev\Travel;

use Illuminate\Support\ServiceProvider;

class TravelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        if (function_exists('config_path')) {
            $this->mergeConfigFrom(
                __DIR__.'/../config/travel.php', 'travel'
            );
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (function_exists('config_path')) {
            // Publish config file
            $this->publishes([
                __DIR__.'/../config/travel.php' => config_path('travel.php'),
            ], 'travel-config');
        }
    }
}
