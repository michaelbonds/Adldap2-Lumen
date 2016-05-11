<?php

namespace MichaelB\Lumen\Adldap\Auth;

use Adldap\Connections\Configuration;
use Adldap\Contracts\Connections\ConnectionInterface;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard as IlluminateGuardContract;
use Adldap\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;

class AdldapGuard implements IlluminateGuardContract
{
    use GuardHelpers;

    /**
     * @var Guard
     */
    protected $ldap_guard;


    /**
     * AdldapGuard constructor.
     *
     * @param UserProvider        $userProvider
     * @param ConnectionInterface $connection
     * @param Configuration       $configuration
     */
    public function __construct(UserProvider $userProvider, ConnectionInterface $connection, Configuration $configuration)
    {
        $this->provider = $userProvider;
        $this->ldap_guard = new Guard($connection, $configuration);
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if ($this->ldap_guard->attempt($credentials['username'], $credentials['password'])) {
            $this->setUser($this->provider->retrieveByCredentials($credentials));
            return true;
        }
        
        return false;
    }

    /**
     * @param array $credentials
     *
     * @return bool
     */
    public function attempt(array $credentials = [])
    {
        return $this->validate($credentials);
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     *
     * @return void
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
    }

    /**
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->ldap_guard, $method)) {
            return call_user_func_array([$this->ldap_guard, $method], $parameters);
        }
    }
}