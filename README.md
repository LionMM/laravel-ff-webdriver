Laravel ff webdriver
========

[![Latest Stable Version](https://poser.pugx.org/lionmm/laravel-ff-webdriver/v/stable)](https://packagist.org/packages/lionmm/laravel-ff-webdriver)
[![Latest Unstable Version](https://poser.pugx.org/lionmm/laravel-ff-webdriver/v/unstable)](https://packagist.org/packages/lionmm/laravel-ff-webdriver)

[![Total Downloads](https://poser.pugx.org/lionmm/laravel-ff-webdriver/downloads)](https://packagist.org/packages/lionmm/laravel-ff-webdriver)

[![License](https://poser.pugx.org/lionmm/laravel-ff-webdriver/license)](https://packagist.org/packages/lionmm/laravel-ff-webdriver)


Selenium Firefox web-driver interface for Laravel 5.2.

This package uses the facebook's [php-webdriver](https://github.com/facebook/php-webdriver) library

## Laravel supporting

* Laravel 5.3.x: `in developing`

* Laravel 5.1.x to 5.2.x: `dev-master`

* Laravel < 5.1: `not supported`

## Quick start

### Laravel 5.1.x to 5.2.x

Run `composer require lionmm/laravel-ff-webdriver dev-master`

In your `config/app.php` add `\LionMM\WebDriver\WebDriverServiceProvider::class,` to the end of the `providers` array

```php
'providers' => array(

    ...
    \LionMM\WebDriver\WebDriverServiceProvider::class,
),
```

Add the `WebDriver` facade to the end of the `aliases` array as well

```php
'aliases' => array(

    ...
    'WebDriver' => \LionMM\WebDriver\Facades\WebDriver::class,
),
```

Now publish the configuration file for laravel-ff-webdriver:

    $ php artisan vendor:publish --provider="LionMM\WebDriver\WebDriverServiceProvider"

## Usage

### Using library

in developing

### Methods

* initDriver
* get
* deleteAllCookies
* getCurrentURL
* waitForElement
* getAllElements
* checkForElement
* switchToDefaultContent
* takeScreenshot
* executeScript

