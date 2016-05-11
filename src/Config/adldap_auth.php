<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Connection
    |--------------------------------------------------------------------------
    |
    | The connection to use for authentication.
    |
    | You must specify connections in your `config/adldap.php` configuration file.
    |
    | This must be a string.
    |
    */

    'connection' => env('ADLDAP_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Username Attribute
    |--------------------------------------------------------------------------
    |
    | The username attribute is an array of the html input name and the LDAP
    | attribute to discover the user by. The reason for this is to hide
    | the attribute that you're using to login users.
    |
    | For example, if your input name is `username` and you'd like users
    | to login by their `samaccountname` attribute, then keep the
    | configuration below. However, if you'd like to login users
    | by their emails, then change `samaccountname` to `mail`.
    | and `username` to `email`.
    |
    | This must be an array with a key - value pair.
    |
    */

    'username_attribute' => ['username' => 'samaccountname'],

    /*
    |--------------------------------------------------------------------------
    | Limitation Filter
    |--------------------------------------------------------------------------
    |
    | The limitation filter allows you to enter a raw filter to only allow
    | specific users / groups / ous to authenticate.
    |
    | This should be a standard LDAP filter.
    |
    */

    'limitation_filter' => env('ADLDAP_LIMITATION_FILTER', ''),

    /*
    |--------------------------------------------------------------------------
    | Login Fallback
    |--------------------------------------------------------------------------
    |
    | The login fallback option allows you to login as a user located on the
    | local database if active directory authentication fails.
    |
    | Set this to true if you would like to enable it.
    |
    | This option must be true or false.
    |
    */

    'login_fallback' => env('ADLDAP_LOGIN_FALLBACK', false),

    /*
    |--------------------------------------------------------------------------
    | Password Key
    |--------------------------------------------------------------------------
    |
    | The password key is the name of the input array key located inside
    | the user input array given to the auth driver.
    |
    | Change this if you change your password fields input name.
    |
    | This option must be a string.
    |
    */

    'password_key' => env('ADLDAP_PASSWORD_KEY', 'password'),

    /*
    |--------------------------------------------------------------------------
    | Login Attribute
    |--------------------------------------------------------------------------
    |
    | The login attribute is the name of the active directory user property
    | that you use to log users in. For example, if your company uses
    | email, then insert `mail`.
    |
    | This option must be a string.
    |
    */

    'login_attribute' => env('ADLDAP_LOGIN_ATTRIBUTE', 'samaccountname'),

    /*
    |--------------------------------------------------------------------------
    | Bind User to Model
    |--------------------------------------------------------------------------
    |
    | The bind user to model option allows you to access the Adldap user model
    | instance on your laravel database model to be able run operations
    | or retrieve extra attributes on the Adldap user model instance.
    |
    | If this option is true, you must insert the trait:
    |
    |   `Adldap\Laravel\Traits\AdldapUserModelTrait`
    |
    | Onto your User model configured in `config/auth.php`.
    |
    | Then use `Auth::user()->adldapUser` to access.
    |
    | This option must be true or false.
    |
    */

    'bind_user_to_model' => env('ADLDAP_BIND_USER_TO_MODEL', false),

    /*
    |--------------------------------------------------------------------------
    | Sync Attributes
    |--------------------------------------------------------------------------
    |
    | Attributes specified here will be added / replaced on the user model
    | upon login, automatically synchronizing and keeping the attributes
    | up to date.
    |
    | The array key represents the Laravel model key, and the value
    | represents the Active Directory attribute to set it to.
    |
    | The users email is already synchronized and does not need to be
    | added to this array.
    |
    */

    'sync_attributes' => [

        'name' => 'cn',

    ],

    /*
    |--------------------------------------------------------------------------
    | Select Attributes
    |--------------------------------------------------------------------------
    |
    | Attributes to select upon the user on authentication and binding.
    |
    | If no attributes are given inside the array, all attributes on the
    | user are selected.
    |
    | ** Note ** : Keep in mind you must include attributes that you would
    | like to synchronize, as well as your login attribute.
    |
    */

    'select_attributes' => [

        //

    ],

];
