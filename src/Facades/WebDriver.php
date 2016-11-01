<?php namespace LionMM\WebDriver\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class WebDriver
 * @package LionMM\WebDriver\Facades
 */
class WebDriver extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'webdriver';
    }
}
