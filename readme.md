# Adldap2 - Laravel

[![Build Status](https://img.shields.io/travis/Adldap2/Adldap2-Laravel.svg?style=flat-square)](https://travis-ci.org/Adldap2/Adldap2-Laravel)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/Adldap2/Adldap2-laravel/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/Adldap2/Adldap2-laravel/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/adldap2/adldap2-laravel.svg?style=flat-square)](https://packagist.org/packages/adldap2/adldap2-laravel)
[![Latest Stable Version](https://img.shields.io/packagist/v/adldap2/adldap2-laravel.svg?style=flat-square)](https://packagist.org/packages/adldap2/adldap2-laravel)
[![License](https://img.shields.io/packagist/l/adldap2/adldap2-laravel.svg?style=flat-square)](https://packagist.org/packages/adldap2/adldap2-laravel)

## Description

Adldap2 - Laravel allows easy configuration, access, and management to active directory utilizing the root
[Adldap2 Repository](http://www.github.com/Adldap2/Adldap2).

It includes:

- An Adldap contract (`Adldap\Contracts\AdldapInterface`) for dependency injection through Laravel's IoC
- An Auth driver for easily allowing users to login to your application using active directory
- An Adldap facade (`Adldap\Laravel\Facades\Adldap`) for easily retrieving the Adldap instance from the IoC
- Support for multiple LDAP connections

## Installation

[Quick Start - From Scratch](quick-start.md)

Insert Adldap2-Laravel into your `composer.json` file:

```json
"michaelb/adldap2-lumen": "1.0.*",
```

Then run `composer update`.

Register the service provider in bootstrap/app.php
```php
// ...
$app->register(MichaelB\Lumen\AdldapServiceProvider::class);
```

Publish the configuration file by running:
```bash
php artisan vendor:publish --tag="adldap"
```

Now you're all set!

## Usage

You can perform all methods on Adldap through its facade like so:
```php
// Finding a user.
$user = Adldap::getProvider('default')->search()->users()->find('john doe');

// Searching for a user.
$search = Adldap::getProvider('default')->search()->where('cn', '=', 'John Doe')->get();

// Authenticating.
if (Adldap::getProvider('default')->auth()->attempt($username, $password)) {
    // Passed!
}
```

Or you can inject the Adldap contract:
```php
use Adldap\Contracts\AdldapInterface;

class UserController extends Controller
{
    /**
     * @var Adldap
     */
    protected $adldap;

    /**
     * Constructor.
     *
     * @param AdldapInterface $adldap
     */
    public function __construct(AdldapInterface $adldap)
    {
        $this->adldap = $adldap;
    }

    /**
     * Displays the all LDAP users.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = $this->adldap->getProvider('default')->search()->users()->get();

        return view('users.index', compact('users'));
    }
}
```

To see more usage in detail, please visit the [Adldap2 Repository](http://github.com/Adldap2/Adldap2);

## Auth Driver

The Adldap Laravel auth driver allows you to seamlessly authenticate active directory users,
as well as have a local database record of the user. This allows you to easily attach information
to the users as you would a regular laravel application.

> **Note**: The Adldap auth driver actually extends from and utilizes Laravel's eloquent auth driver.

### Installation

#### Laravel 5.1

Insert the `AdldapAuthServiceProvider` into your `config/app.php` file:

```php
Adldap\Laravel\AdldapAuthServiceProvider::class,
```

Publish the auth configuration:

```bash
php artisan vendor:publish --tag="adldap"
```

Change the auth driver in `config/auth.php` to `adldap`:

```php
/*
|--------------------------------------------------------------------------
| Default Authentication Driver
|--------------------------------------------------------------------------
|
| This option controls the authentication driver that will be utilized.
| This driver manages the retrieval and authentication of the users
| attempting to get access to protected areas of your application.
|
| Supported: "database", "eloquent"
|
*/

'driver' => 'adldap',
```

#### Laravel 5.2

Insert the `AdldapAuthServiceProvider` into your `config/app.php` file:

```php
Adldap\Laravel\AdldapAuthServiceProvider::class,
```

Publish the auth configuration:

```bash
php artisan vendor:publish --tag="adldap"
```

Open your `config/auth.php` configuration file and change the following:

Change the `provider` entry inside the `web` authentication guard:

```php
/*
|--------------------------------------------------------------------------
| Authentication Guards
|--------------------------------------------------------------------------
|
| Next, you may define every authentication guard for your application.
| Of course, a great default configuration has been defined for you
| here which uses session storage and the Eloquent user provider.
|
| All authentication drivers have a user provider. This defines how the
| users are actually retrieved out of your database or other storage
| mechanisms used by this application to persist your user's data.
|
| Supported: "session", "token"
|
*/

'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'adldap',
    ],
    'api' => [
        'driver' => 'token',
        'provider' => 'users',
    ],
],
```

Now add the `adldap` provider to your `providers` array:

```php
/*
|--------------------------------------------------------------------------
| User Providers
|--------------------------------------------------------------------------
|
| All authentication drivers have a user provider. This defines how the
| users are actually retrieved out of your database or other storage
| mechanisms used by this application to persist your user's data.
|
| If you have multiple user tables or models you may configure multiple
| sources which represent each model / table. These sources may then
| be assigned to any extra authentication guards you have defined.
|
| Supported: "database", "eloquent"
|
*/
'providers' => [
    'adldap' => [
        'driver' => 'adldap',
        'model' => App\User::class,
    ],
    'users' => [
        'driver' => 'eloquent',
        'model' => App\User::class,
    ],
    // 'users' => [
    //     'driver' => 'database',
    //     'table' => 'users',
    // ],
],
```

### Usage

#### Username Attributes

Inside your `config/adldap_auth.php` file there is a configuration option named `username_attribute`. The key of the
array indicates the input name of your login form, and the value indicates the LDAP attribute that this references.

This option just allows you to set your input name to however you see fit, and allow different ways of logging in a user.

In your login form, change the username form input name to your configured input name.

By default this is set to `email`:
```html
<input type="text" name="email" />

<input type="password" name="password" />
```

You'll also need to add the following to your AuthController if you're not overriding the default postLogin method.
```php
protected $username = 'email';
```

If you'd like to use the users `samaccountname` to login instead, just change your input name and auth configuration:
```html
<input type="text" name="username" />

<input type="password" name="password" />
```

> **Note**: If you're using the `username` input field, make sure you have the `username` field inside your users database
table as well. By default, laravel's migrations use the `email` field.

Inside `config/adldap_auth.php`
```php
'username_attribute' => ['username' => 'samaccountname'],
```

> **Note**: The actual authentication is done with the `login_attribute` inside your `config/adldap_auth.php` file.

#### Logging In

Login a user regularly using `Auth::attempt($credentials);`. Using `Auth::user()` when a user is logged in
will return your configured `App\User` model in `config/auth.php`.

#### Synchronizing Attributes

Inside your `config/adldap_auth.php` file there is a configuration option named `sync_attributes`. This is an array
of attributes where the key is the `User` model attribute, and the value is the active directory users attribute.

By default, the `User` models `name` attribute is synchronized to the AD users `cn` attribute. This means, upon login,
the users `name` attribute on Laravel `User` Model will be set to the active directory common name (`cn`) attribute, **then saved**.

Feel free to add more attributes here, however be sure that your `users` database table contains the key you've entered.

##### Sync Attribute Callbacks

> **Note**: This feature was introduced in `v1.3.8`.

If you're looking to synchronize an attribute from an Adldap model that contains an array or an object, you can use a callback
to return a specific value to your Laravel model's attribute. For example:

```php
'sync_attributes' => [

    'name' => 'App\Handlers\LdapAttributeHandler@name',

],
```

The `LdapAttributeHandler` class:

```php
namespace App\Handlers;

use Adldap\Models\User;

class LdapAttributeHandler
{
    /**
     * Returns the common name of the AD User.
     *
     * @param User $user
     *
     * @return string
     */
    public function name(User $user)
    {
        return $user->getAccountName();
    }
}
```

> **Note**: Attribute handlers are constructed using the `app()` helper. This means you can type-hint any application
> dependencies you may need in the handlers constructor.

#### Binding the Adldap User Model to the Laravel User Model

> **Note**: Before we begin, enabling this option will perform a single query on your AD server for a logged in user
**per request**. Eloquent already does this for authentication, however this could lead to slightly longer load times
(depending on your AD server and network speed of course).

Inside your `config/adldap_auth.php` file there is a configuration option named `bind_user_to_model`. Setting this to
true sets the `adldapUser` property on your configured auth User model to the Adldap User model. For example:
```php    
if (Auth::attempt($credentials)) {
    $user = Auth::user();

    var_dump($user); // Returns instance of App\User;

    var_dump($user->adldapUser); // Returns instance of Adldap\Models\User;

    // Retrieving the authenticated LDAP users groups
    $groups = $user->adldapUser->getGroups();
}
```

You **must** insert the trait `Adldap\Laravel\Traits\AdldapUserModelTrait` onto your configured auth User model, **OR**
Add the public property `adldapUser` to your model.

```php
<?php
// app/User.php

namespace App;

use Adldap\Laravel\Traits\AdldapUserModelTrait;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, AdldapUserModelTrait; // Insert trait here

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
}
```

#### Login Fallback

> **Note**: This feature was introduced in `v1.3.9`. You'll will need to re-publish the Adldap Auth configuration file
to receive this option.

The login fallback option allows you to login as a local database user using the Eloquent authentication driver if
active directory authentication fails. This option would be handy in environments where:

- You may have some active directory users and other users registering through the website itself (user does not exist in your AD).
- Local development where your AD server may be unavailable

To enable it, simply set the option to true in your `adldap_auth.php` configuration file:

```php
'login_fallback' => false, // Set to true.
```

#### Windows Authentication (SSO) Middleware

> **Note**: This feature was introduced in `v1.4.3`. You will need to re-publish the Adldap Auth configuration file
to receive this option.

> **Requirements**: This feature assumes that you have enabled `Windows Authentication` in IIS, or have enabled it
in some other means with Apache. Adldap does not set this up for you. To enable Windows Authentication, visit:
https://www.iis.net/configreference/system.webserver/security/authentication/windowsauthentication/providers/add

SSO authentication allows you to authenticate your users by the pre-populated `$_SERVER['AUTH_USER']` (or `$_SERVER['REMOTE_USER`])
that is filled when users visit your site when SSO is enabled on your server. This is configurable in your `adldap_auth.php`
configuration file.

To use the middleware, insert it on your middleware stack:

```php
protected $middlewareGroups = [
    'web' => [
        Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        Middleware\VerifyCsrfToken::class,
        \Adldap\Laravel\Middleware\WindowsAuthenticate::class, // Inserted here.
    ],
];
```

Now when you visit your site, a user account will be created (if one doesn't exist already)
with a random 16 character string password and then automatically logged in. Neat huh?

#### Login Limitation Filter

> **Note**: This feature was introduced in `v1.4.6`. You will need to re-publish the Adldap Auth configuration file
to receive this option.

Inside of your `config/adldap_auth.php` configuration, you can now insert a raw LDAP filter to specify which users are allowed to authenticate.

This filter persists to the Windows Authentication Middleware as well.

For example, to allow only users to that contain an email address to login, insert the filter: `(mail=*)`:

```php
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

 'limitation_filter' => '(mail=*)',
```

For another example, here's how you can limit users logging in that are apart of a specific group:

> **Note**: This will also allow nested group users to login as well.

```php
'limitation_filter' => '(memberof:1.2.840.113556.1.4.1941:=CN=MyGroup,DC=example,DC=com)',
```

#### Multiple Authentication Connections

> **Note**: This feature was introduced in `v2.0.0`.

To swap connections on the fly, set your configurations default connection and try re-authenticating the user:

```php
$auth = false;

if (Auth::attempt($credentials)) {
    $auth = true; // Logged in successfully
} else {
    // Login failed, swap and try other connection.
    Config::set('adldap_auth.connection', 'other-connection');

    if (Auth::attempt($credentials)) {
        $auth = true; // Passed logging in with other connection.
    }
}

if ($auth === true) {
    return $this->handleUserWasAuthenticated($request, $throttles);
}

return 'Login incorrect!';
```
