Laravel ff webdriver
========

[![Latest Stable Version](https://poser.pugx.org/lionmm/laravel-ff-webdriver/v/stable)](https://packagist.org/packages/lionmm/laravel-ff-webdriver)
[![Latest Unstable Version](https://poser.pugx.org/lionmm/laravel-ff-webdriver/v/unstable)](https://packagist.org/packages/lionmm/laravel-ff-webdriver)

[![Total Downloads](https://poser.pugx.org/lionmm/laravel-ff-webdriver/downloads)](https://packagist.org/packages/lionmm/laravel-ff-webdriver)

[![License](https://poser.pugx.org/lionmm/laravel-ff-webdriver/license)](https://packagist.org/packages/lionmm/laravel-ff-webdriver)


Selenium Firefox web-driver interface for Laravel 5.2.

This package uses the facebook's [php-webdriver](https://github.com/facebook/php-webdriver) library

Collect all mostly used methods in one class and give providers and aliases for working vs it 

## Laravel supporting

* Laravel 5.3.x: `in developing`

* Laravel 5.1.x to 5.2.x: `dev-master`

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

Now publish the configuration file for laravel-ff-webdriver (if need it):

    $ php artisan vendor:publish --provider="LionMM\WebDriver\WebDriverServiceProvider"

## Usage

### Selenium requirement

For using library you must install and run [Selenium server](http://www.seleniumhq.org/download/)

By default, library try to connect to `http://localhost:4444/wd/hub`

You can change that path in `config/webdriver.php` _(see below)_

### Using library

Best way - using Facade by alias `WebDriver::method()`

Also, you can make instance of `\LionMM\WebDriver\WebDriver` class
```php
<?php

use LionMM\WebDriver\WebDriver;

class Foo {
    public function Bar()
    {
        $webDriver = \App::make(WebDriver::class); // use \App::make for DI
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

