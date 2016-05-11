<?php

namespace MichaelB\Lumen\Adldap;

use Adldap\Connections\Configuration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use MichaelB\Lumen\Adldap\Auth\AdldapGuard;

class AdldapAuthServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Run service provider boot operations.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->configure('adldap_auth');
        $config = config('adldap_auth');

        $auth = app('auth');
        
        $auth->extend('adldap', function ($app, $name) use ($config) {
            $connection = $app[$name]->getProvider($config['connection'])->getConnection();
            
            $provider = new AdldapAuthUserProvider($app['hash'], $app['config']['auth.providers.adldap.model']);
            $conneciton_settings = config('adldap')['connections'][$config['connection']]['connection_settings'];
            return new AdldapGuard($provider, $connection, new Configuration($conneciton_settings));
            
//            return new AdldapAuthUserProvider($app['hash'], $app['config']['auth.providers.adldap.model']);
        });

//        if (method_exists($auth, 'provider')) {
//            // If the provider method exists, we're running Laravel 5.2.
//            // Register the adldap auth user provider.
//            $auth->provider('adldap', function ($app, array $config) {
//                return new AdldapAuthUserProvider($app['hash'], $config['model']);
//            });
//        } else {
//            // Otherwise we're using 5.0 || 5.1
//            // Extend Laravel authentication with Adldap driver.
//            $auth->extend('adldap', function ($app) {
//                return new AdldapAuthUserProvider($app['hash'], $app['config']['auth.model']);
//            });
//        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['auth'];
    }
}
