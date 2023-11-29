<?php

namespace App\Providers;

use DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Facades\Octane;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment('local')) {
            URL::forceScheme('https');
            \DB::listen(function ($query) {
                \Log::info(
                    $query->sql,
                    $query->bindings,
                    $query->time
                );
            });
        }

        Octane::tick('scheduler', function () {
            Artisan::call('schedule:run');
        })->seconds(60)->immediate();


    }
}
