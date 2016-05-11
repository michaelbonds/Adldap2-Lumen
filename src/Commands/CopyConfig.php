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
        $config_dir = $this->basePath().'/'.$this->appConfigDirectory();
        
        if (! $this->isDirectory($config_dir)) {
            $this->laravel->make('files')->makeDirectory($config_dir);
        }
        
        $this->copyAdldapAuth();
        $this->copyAdldapConfig();
        $this->copyLumenAuthConfig();
        
        $this->comment("config files copied!");
    }

    /**
     * @return int
     */
    private function copyAdldapAuth()
    {
        $config_path = $this->configPath();
        $from = "$config_path/adldap_auth.php";
        $to = $this->toAppConfigPath('adldap_auth.php');
        
        return $this->copy($from, $to);
    }

    /**
     * @return int
     */
    private function copyAdldapConfig()
    {
        $config_path = $this->configPath();
        $from = "$config_path/adldap.php";
        $to = $this->toAppConfigPath('adldap.php');
        
        return $this->copy($from, $to);
    }

    /**
     * @return int
     */
    private function copyLumenAuthConfig()
    {
        $base_path = $this->basePath();
        $from = "$base_path/vendor/laravel/lumen-framework/config/auth.php";
        $to = $this->toAppConfigPath('auth.php');
        
        return $this->copy($from, $to);
    }

    /**
     * @return string
     */
    private function basePath()
    {
        return $this->laravel->basePath();
    }

    /**
     * @return string
     */
    private function appConfigDirectory()
    {
        $directory = $this->option('directory');
        return ($directory ?: 'config');
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    private function toAppConfigPath($filename = '')
    {
        $base_path = $this->basePath();
        $config = $this->appConfigDirectory();

        return "$base_path/$config/$filename";
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    private function copy($from = '', $to = '')
    {
        if ( ! $this->exists($to)) {
            return copy($from, $to);
        }
        
        $this->error("$to already exists!");
    }

    /**
     * @param string $path
     *
     * @return mixed
     */
    private function exists($path = '')
    {
        return $this->laravel->make('files')->exists($path);
    }

    /**
     * @return string
     */
    private function configPath()
    {
        return __DIR__.'/../Config';
    }

    /**
     * @param $config_dir
     *
     * @return bool
     */
    private function isDirectory($config_dir)
    {
        return $this->laravel->make('files')->isDirectory($config_dir);
    }

}
