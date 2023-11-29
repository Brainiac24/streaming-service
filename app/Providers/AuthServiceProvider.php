<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Guards\JwtGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use phpcent\Client;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::provider('chache_user', function ($app, array $config) {
            return new CacheUserProvider($this->app['hash'], $config['model']);
        });

        Auth::extend('jwt', function ($app, $name, array $config) {
            return new JwtGuard(Auth::createUserProvider($config['provider']), $app->make('request'));
        });

        $this->app->when(Client::class)
            ->needs('$url')
            ->giveConfig('services.centrifugo.url');
    }
}
