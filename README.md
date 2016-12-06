Laravel ff webdriver
========

[![Latest Stable Version](https://img.shields.io/packagist/v/lionmm/laravel-ff-webdriver.svg?style=flat-square)](https://packagist.org/packages/lionmm/laravel-ff-webdriver)
[![Total Downloads](https://img.shields.io/packagist/dt/lionmm/laravel-ff-webdriver.svg?style=flat-square)](https://packagist.org/packages/lionmm/laravel-ff-webdriver)
[![License](https://img.shields.io/packagist/l/lionmm/laravel-ff-webdriver.svg?style=flat-square)](https://packagist.org/packages/lionmm/laravel-ff-webdriver)

[![GitHub tag](https://img.shields.io/github/tag/LionMM/laravel-ff-webdriver.svg?style=flat-square)]()
[![GitHub release](https://img.shields.io/github/release/LionMM/laravel-ff-webdriver.svg?style=flat-square)]()
[![Github All Releases](https://img.shields.io/github/downloads/LionMM/laravel-ff-webdriver/total.svg?style=flat-square)]()


Selenium Firefox web-driver interface for Laravel 5.2.

This package uses the facebook's [php-webdriver](https://github.com/facebook/php-webdriver) library

Collect all mostly used methods in one class and give providers and aliases for working vs it 

## Laravel supporting

* Laravel 5.3.x: `in developing`

* Laravel 5.2.x: `1.0.1`

* Laravel 5.1.x: `0.1.2`

* Laravel < 5.1: `not supported`

## Quick start

### Laravel 5.1.x to 5.2.x

Run `composer require lionmm/laravel-ff-webdriver dev-master`

In your `config/app.php` add `LionMM\WebDriver\WebDriverServiceProvider::class,` to the end of the `providers` array

```php
'providers' => array(

    ...
    LionMM\WebDriver\WebDriverServiceProvider::class,
),
```

Add the `WebDriver` facade to the end of the `aliases` array as well

```php
'aliases' => array(

    ...
    'WebDriver' => LionMM\WebDriver\Facades\WebDriver::class,
),
```

Now publish the configuration file for laravel-ff-webdriver _(if need it)_:

    $ php artisan vendor:publish --provider="LionMM\WebDriver\WebDriverServiceProvider"

## Usage

### Selenium requirement

For using library you must install and run [Selenium server](http://www.seleniumhq.org/download/)

By default, library try to connect to `http://localhost:4444/wd/hub`

You can change that path in `config/webdriver.php` _(see above)_

### Using library

Best way - using Facade by alias `WebDriver::method()`

Also, you can make instance of `\LionMM\WebDriver\WebDriver` class
```php
<?php

use LionMM\WebDriver\WebDriver;

class Foo {

    public function Bar()
    {
        $webDriver = \App::make('webdriver'); // use \App::make for DI and 'webdriver' for singleton
    }
}
```

OR

```php
<?php

use LionMM\WebDriver\WebDriver;

class Foo {

    /** @var WebDriver  */
    private $webDriver;

    public function __construct(WebDriver $webDriver)
    {
        $this->webDriver = $webDriver;
    }
}
```

Next step - you must init Driver with method `initDriver($parameters, $request_time_limit)`

```php
<?php

\WebDriver::initDriver(['lang' => 'en', 'no-flash' => true, 'proxy' => '220.155.15.133:8080'], 50000);

```
Instead, the parameter `lang` You can specify directly `'accept_languages' => 'ru-RU,ru,en,en-US,uk'`

By default flash is enabled and `accept_languages` set to `ru-RU,ru,en,en-US,uk`


#### Go to url

Use method `get($url)` for  load a new web page in the current browser window.

#### Operate vs page content

* `waitForElement($selector, $index, $timeout)`
* `checkForElement($selector)`
* `getAllElements($selector, $need, $timeout)`

##### Frames

* `switchToFrame($element)`: Switch to the iframe by its id or name.
* `switchToDefaultContent()`: Switch to the main document if the page contains iframes. Otherwise, switch to the first frame on the page.

#### Service functions

* `quit($wait = 0)`: run automatically in `__destruct()` method. Quits driver, closing every associated window. (hope this...)
* `deleteAllCookies()`: Delete all the cookies that are currently visible.
* `getCookies()`: Get all the cookies for the current domain.
* `getCurrentURL()`: Get a string representing the current URL that the browser is looking at
* `getTitle()`: Get the title of the current page.
* `getPageSource()`: Get the source of the last loaded page.
* `takeScreenshot($pathToSave)`:  Take a screenshot of the current page. if parameter `$pathToSave` not set method return the screenshot contents in PNG format
* `executeScript($jsCode, $arguments, $async)`: Inject a snippet of JavaScript into the page for execution in the context of the currently selected frame. The result of evaluating the script will be returned.

