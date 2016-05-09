<?php

namespace MichaelB\Lumen;

use Adldap\Laravel\Traits\ImportsUsers;
use Adldap\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class AdldapAuthUserProvider extends EloquentUserProvider
{
    use ImportsUsers;

    /**
     * {@inheritdoc}
     */
    public function retrieveById($identifier)
    {
        $model = parent::retrieveById($identifier);

        return $this->discoverAdldapFromModel($model);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = parent::retrieveByToken($identifier, $token);

        return $this->discoverAdldapFromModel($model);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveByCredentials(array $credentials)
    {
        // Get the search query for users only.
        $query = $this->newAdldapUserQuery();

        // Make sure the connection is bound
        // before we try to utilize it.
        if ($query->getConnection()->isBound()) {
            // Get the username input attributes.
            $attributes = $this->getUsernameAttribute();

            // Get the input key.
            $key = key($attributes);

            // Filter the query by the username attribute and retrieve the first user result.
            $user = $query->where([$attributes[$key] => $credentials[$key]])->first();

            // If the user is an Adldap User model instance.
            if ($user instanceof User) {
                // Retrieve the users login attribute.
                $username = $user->{$this->getLoginAttribute()};

                if (is_array($username)) {
                    // We'll make sure we retrieve the users first username
                    // attribute if it's contained in an array.
                    $username = Arr::get($username, 0);
                }

                // Retrieve the password from the submitted credentials.
                $password = Arr::get($credentials, $this->getPasswordKey());

                // Try to log the user in.
                if (!is_null($password) && $this->authenticate($username, $password)) {
                    // Login was successful, we'll create a new
                    // Laravel model with the Adldap user.
                    return $this->getModelFromAdldap($user, $password);
                }
            }
        }

        if ($this->getLoginFallback()) {
            // Login failed. If login fallback is enabled
            // we'll call the eloquent driver.
            return parent::retrieveByCredentials($credentials);
        }
    }

    /**
     * Retrieves the Adldap User model from the
     * specified Laravel model.
     *
     * @param mixed $model
     *
     * @return null|Authenticatable
     */
    protected function discoverAdldapFromModel($model)
    {
        if ($model instanceof Authenticatable && $this->getBindUserToModel()) {
            $attributes = $this->getUsernameAttribute();

            $key = key($attributes);

            $user = $this->newAdldapUserQuery()
                ->where([$attributes[$key] => $model->{$key}])
                ->first();

            if ($user instanceof User) {
                $model = $this->bindAdldapToModel($user, $model);
            }
        }

        return $model;
    }

    /**
     * Authenticates a user against Active Directory.
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    protected function authenticate($username, $password)
    {
        return $this->getAdldap()->auth()->attempt($username, $password);
    }

    /**
     * Returns the password key to retrieve the
     * password from the user input array.
     *
     * @return mixed
     */
    protected function getPasswordKey()
    {
        return Config::get('adldap_auth.password_key', 'password');
    }

    /**
     * Retrieves the Adldap login fallback option for falling back
     * to the local database if AD authentication fails.
     *
     * @return bool
     */
    protected function getLoginFallback()
    {
        return Config::get('adldap_auth.login_fallback', false);
    }
}
