<?php

namespace MichaelB\Lumen\Adldap\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CopyConfig extends Command
{
    /**
     * @var string
     */
    protected $signature = 'adldap:config {--directory= : The config directory}';

    /**
     * @var string
     */
    protected $description = 'Copy configuration to config directory';

    /**
     * @var \Laravel\Lumen\Application
     */
    protected $lumen;

    /**
     * @var string
     */
    protected $configPath;

    /**
     * CopyConfig constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->lumen = $this->laravel;
        $this->configPath = __DIR__.'/../Config/';
    }

    /**
     * Copy the configz
     */
    public function handle()
    {
        $this->copyAuth();
        $this->copyConfig();
    }

    /**
     * @return int
     */
    private function copyAuth()
    {
        $from = $this->configPath.'auth.php';
        $to = $this->getBasePath().'/'.$this->getAppConfigDirectory().'/adldap_auth.php';

        return $this->copy($from, $to);
    }

    /**
     * @return int
     */
    private function copyConfig()
    {
        $from = $this->configPath.'config.php';
        $to = $this->getBasePath().'/'.$this->getAppConfigDirectory().'/adldap.php';
        
        return $this->copy($from, $to);
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return int
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function copy($from = '', $to = '')
    {
        $filesystem = $this->lumen->make('files');
        
        $file = $filesystem->get($from);
        
        return $filesystem->put($to, $file);
    }

    /**
     * @return string
     */
    private function getBasePath()
    {
        return $this->lumen->basePath();
    }

    /**
     * @return string
     */
    private function getAppConfigDirectory()
    {
        $directory = $this->option('directory');
        return ($directory ?: 'config');
    }

}
