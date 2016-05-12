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

        $auth = app('auth');
        
        $auth->extend('adldap', function ($app, $name) {
            $config = config('adldap_auth');
            
            $connection = $app[$name]->getProvider($config['connection'])->getConnection();
            $provider = new AdldapAuthUserProvider($app['hash'], $app['config']['auth.providers.adldap.model']);
            $connection_settings = config('adldap')['connections'][$config['connection']]['connection_settings'];
            
            return new AdldapGuard($provider, $connection, new Configuration($connection_settings));
        });
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
