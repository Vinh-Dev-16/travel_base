<?php

namespace Vinhdev\Travel;


use Illuminate\Support\ServiceProvider;
use Vinhdev\Travel\Contracts\Lib\RedisLib;
use Vinhdev\Travel\Contracts\Lib\RedisLibContract;
use Vinhdev\Travel\Contracts\Lib\RedisProvider;
use Vinhdev\Travel\Contracts\Lib\RedisProviderContract;
use Vinhdev\Travel\Middleware\ValidateBaseRequest;

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

        $this->app->bind(
            RedisLibContract::class,
            RedisLib::class
        );

        // Redis Provider singleton for per-db instances
        $this->app->singleton(
            RedisProviderContract::class,
            RedisProvider::class
        );
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

        // Lấy router từ container thay vì inject
        $router = $this->app->make('router');

        $router->aliasMiddleware('validate.base.request', ValidateBaseRequest::class);

        // Hoặc tự động áp dụng cho tất cả route
        $router->pushMiddlewareToGroup('web', ValidateBaseRequest::class);
        $router->pushMiddlewareToGroup('api', ValidateBaseRequest::class);
    }
}
