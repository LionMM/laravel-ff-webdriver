<?php namespace LionMM\WebDriver;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Class WebDriverServiceProvider
 * @package LionMM\WebDriver
 */
class WebDriverServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('webdriver', function (Application $app) {
            return $app->make(WebDriver::class);
        });
    }
}
