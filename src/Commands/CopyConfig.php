<?php

namespace MichaelB\Lumen\Adldap\Commands;

use Illuminate\Console\Command;

class CopyConfig extends Command
{
    /**
     * @var string
     */
    protected $signature = 'adldap:config';

    /**
     * @var string
     */
    protected $description = 'Copy configuration to config directory';

    /**
     * CopyConfig constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Copy the configz
     */
    public function handle()
    {
        dd(app('storage'));
    }
    
}
