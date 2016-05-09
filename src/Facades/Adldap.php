<?php

namespace MichaelB\Lumen\Facades;

use Illuminate\Support\Facades\Facade;

class Adldap extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'adldap';
    }
}
