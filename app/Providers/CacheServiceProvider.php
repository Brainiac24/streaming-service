<?php

namespace App\Providers;

use App\Services\Cache\CacheService;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('cacheService', function () {
            return new CacheService();
        });
    }
}
