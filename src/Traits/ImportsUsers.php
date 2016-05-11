<?php

namespace MichaelB\Lumen\Adldap\Traits;

use MichaelB\Lumen\Adldap\Facades\Adldap;
use Adldap\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

trait ImportsUsers
{
    /**
     * {@inheritdoc}
     */
    abstract public function createModel();

    /**
     * Creates a local User from Active Directory.
     *
     * @param User   $user
     * @param string $password
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getModelFromAdldap(User $user, $password)
    {
        // Get the username attributes.
        $attributes = $this->getUsernameAttribute();

        // Get the model key.
        $key = key($attributes);

        // Get the username from the AD model.
        $username = $user->{$attributes[$key]};

        // Make sure we retrieve the first username
        // result if it's an array.
        if (is_array($username)) {
            $username = Arr::get($username, 0);
        }
        
        // Try to retrieve the model from the model key and AD username.
        $model = $this->createModel()->newQuery()->where([$key => $username])->first();

        // Create the model instance of it isn't found.
        if (!$model instanceof Model) {
            $model = $this->createModel();
        }

        // Set the username and password in case
        // of changes in active directory.
        $model->{$key} = $username;

        // Sync the users password.
        $model = $this->syncModelPassword($model, $password);

        // Synchronize other active directory
        // attributes on the model.
        $model = $this->syncModelFromAdldap($user, $model);

        if ($this->getBindUserToModel()) {
            $model = $this->bindAdldapToModel($user, $model);
        }

        return $model;
    }

    /**
     * Binds the Adldap User instance to the Eloquent model instance
     * by setting its `adldapUser` public property.
     *
     * @param User            $user
     * @param Authenticatable $model
     *
     * @return Authenticatable
     */
    protected function bindAdldapToModel(User $user, Authenticatable $model)
    {
        $model->adldapUser = $user;

        return $model;
    }

    /**
     * Fills a models attributes by the specified Users attributes.
     *
     * @param User            $user
     * @param Authenticatable $model
     *
     * @return Authenticatable
     */
    protected function syncModelFromAdldap(User $user, Authenticatable $model)
    {
        $attributes = $this->getSyncAttributes();

        foreach ($attributes as $modelField => $adField) {
            if ($this->isAttributeCallback($adField)) {
                $value = $this->handleAttributeCallback($user, $adField);
            } else {
                $value = $this->handleAttributeRetrieval($user, $adField);
            }

            $model->{$modelField} = $value;
        }

        if ($model instanceof Model) {
            $model->save();
        }

        return $model;
    }

    /**
     * Syncs the models password with the specified password.
     *
     * @param Authenticatable $model
     * @param string          $password
     *
     * @return Authenticatable
     */
    protected function syncModelPassword(Authenticatable $model, $password)
    {
        if ($model instanceof Model && $model->hasSetMutator('password')) {
            // If the model has a set mutator for the password then
            // we'll assume that the dev is using their
            // own encryption method for passwords.
            $model->password = $password;

            return $model;
        }

        // Always encrypt the model password by default.
        $model->password = app('hash')->make($password);

        return $model;
    }

    /**
     * Returns true / false if the specified string
     * is a callback for an attribute handler.
     *
     * @param string $string
     *
     * @return bool
     */
    protected function isAttributeCallback($string)
    {
        $matches = preg_grep("/(\w)@(\w)/", explode("\n", $string));

        return count($matches) > 0;
    }

    /**
     * Handles retrieving the value from an attribute callback.
     *
     * @param User   $user
     * @param string $callback
     *
     * @return mixed
     */
    protected function handleAttributeCallback(User $user, $callback)
    {
        // Explode the callback into its class and method.
        list($class, $method) = explode('@', $callback);

        // Create the handler.
        $handler = app($class);

        // Call the attribute handler method and return the result.
        return call_user_func_array([$handler, $method], [$user]);
    }

    /**
     * Handles retrieving the specified field from the User model.
     *
     * @param User   $user
     * @param string $field
     *
     * @return string|null
     */
    protected function handleAttributeRetrieval(User $user, $field)
    {
        if ($field === $this->getSchema()->thumbnail()) {
            // If the field we're retrieving is the users thumbnail photo, we need
            // to retrieve it encoded so we're able to save it to the database.
            $value = $user->getThumbnailEncoded();
        } else {
            $value = $user->{$field};

            if (is_array($value)) {
                // If the AD Value is an array, we'll
                // retrieve the first value.
                $value = Arr::get($value, 0);
            }
        }

        return $value;
    }

    /**
     * Returns a new Adldap user query.
     *
     * @return \Adldap\Query\Builder
     */
    protected function newAdldapUserQuery()
    {
        $query = $this->getAdldap()->search()->users();

        $filter = $this->getLimitationFilter();

        if (!empty($filter)) {
            // If we're provided a login limitation filter,
            // we'll add it to the user query.
            $query->rawFilter($filter);
        }

        return $query->select($this->getSelectAttributes());
    }

    /**
     * Returns Adldap's current attribute schema.
     *
     * @return \Adldap\Contracts\Schemas\SchemaInterface
     */
    protected function getSchema()
    {
        return $this->getAdldap()->getSchema();
    }

    /**
     * Returns the root Adldap instance.
     *
     * @param string $provider
     *
     * @return \Adldap\Contracts\Connections\ProviderInterface
     */
    protected function getAdldap($provider = null)
    {
        /** @var \Adldap\Adldap $ad */
        $ad = Adldap::getFacadeRoot();

        if (is_null($provider)) {
            $provider = $this->getDefaultConnectionName();
        }

        return $ad->getManager()->get($provider);
    }

    /**
     * Retrieves the Aldldap select attributes when performing
     * queries for authentication and binding for users.
     *
     * @return array
     */
    protected function getSelectAttributes()
    {
        return Config::get('adldap_auth.select_attributes', []);
    }

    /**
     * Returns the username attribute for discovering LDAP users.
     *
     * @return array
     */
    protected function getUsernameAttribute()
    {
        return Config::get('adldap_auth.username_attribute', ['username' => $this->getSchema()->accountName()]);
    }

    /**
     * Retrieves the Adldap bind user to model config option for binding
     * the Adldap user model instance to the laravel model.
     *
     * @return bool
     */
    protected function getBindUserToModel()
    {
        return Config::get('adldap_auth.bind_user_to_model', false);
    }

    /**
     * Retrieves the Adldap login attribute for authenticating users.
     *
     * @return string
     */
    protected function getLoginAttribute()
    {
        return Config::get('adldap_auth.login_attribute', $this->getSchema()->accountName());
    }

    /**
     * Retrieves the Adldap sync attributes for filling the
     * Laravel user model with active directory fields.
     *
     * @return array
     */
    protected function getSyncAttributes()
    {
        return Config::get('adldap_auth.sync_attributes', ['name' => $this->getSchema()->commonName()]);
    }

    /**
     * Returns the configured login limitation filter.
     *
     * @return string|null
     */
    protected function getLimitationFilter()
    {
        return Config::get('adldap_auth.limitation_filter');
    }

    /**
     * Retrieves the default connection name from the configuration.
     *
     * @return mixed
     */
    protected function getDefaultConnectionName()
    {
        return Config::get('adldap_auth.connection', 'default');
    }
}
