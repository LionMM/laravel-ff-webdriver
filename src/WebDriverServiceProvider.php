<?php namespace LionMM\WebDriver;

use Illuminate\Support\ServiceProvider;

/**
 * Class WebDriverServiceProvider
 * @package LionMM\WebDriver
 */
class WebDriverServiceProvider extends ServiceProvider
{
    /**
     *
     */
    public function boot()
    {
        $configPath = __DIR__ . '/config/webdriver.php';
        $this->publishes([$configPath => $this->getConfigPath()], 'config');
    }

    /**
     *
     */
    public function register()
    {
        $configPath = __DIR__ . '/config/webdriver.php';
        $this->mergeConfigFrom($configPath, 'webdriver');

        $this->app->singleton('webdriver', function ($app) {
            return new WebDriver($app);
        });
    }

    /**
     * Get the config path
     *
     * @return string
     */
    protected function getConfigPath()
    {
        return config_path('webdriver.php');
    }

    /**
     * Publish the config file
     *
     * @param  string $configPath
     */
    protected function publishConfig($configPath)
    {
        $this->publishes([$configPath => config_path('webdriver.php')], 'config');
    }
}
