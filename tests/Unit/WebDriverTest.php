<?php

namespace Tests\Unit;

use Facebook\WebDriver\Firefox\FirefoxProfile;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\Remote\DriverCommand;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverCapabilities;
use Facebook\WebDriver\WebDriverOptions;
use Facebook\WebDriver\WebDriverTargetLocator;
use Facebook\WebDriver\WebDriverWait;
use LionMM\WebDriver\WebDriver;
use Prophecy\Argument;
use Symfony\Component\Console\Output\ConsoleOutput;

class WebDriverTest extends \PHPUnit_Framework_TestCase
{
    private $consoleOutput;

    public function setUp()
    {
        $consoleOutput = $this->prophesize(ConsoleOutput::class);
        $consoleOutput->writeln(Argument::any())->willReturn(true);
        $consoleOutput->write(Argument::any())->willReturn(true);
        $this->consoleOutput = $consoleOutput->reveal();
    }

    public function testWebDriver_instance()
    {
        $webDriver = new WebDriver($this->consoleOutput, new FirefoxProfile());

        $this->assertInstanceOf(WebDriver::class, $webDriver);
    }

//    public function testWebDriver_initDriver()
//    {
//        $remoteWebDriverClass = $this->prophesize(RemoteWebDriver::class);
//        $remoteWebDriverClass->create()->willReturn('boo');
//
//
//        $webDriver = new WebDriver($this->consoleOutput, new FirefoxProfile());
//
//        // set the mock classname in the property
//        $reflProp = new \ReflectionProperty(WebDriver::class, 'remoteWebDriverClass');
//        $reflProp->setAccessible(true);
//        $reflProp->setValue($webDriver, $remoteWebDriverClass->reveal());
//
////        $reflMethod = new \ReflectionMethod(WebDriver::class, 'createDriverInstance');
////        $reflMethod->;
//
//        $webDriver->initDriver();
//    }

    public function testWebDriver_get()
    {
        $testUrl = 'http://test.url';
        $webDriver = new WebDriver($this->consoleOutput, new FirefoxProfile());

        $driver = $this->prophesize(\Facebook\WebDriver\WebDriver::class);
        $driver->quit()->shouldBeCalledTimes(1);

        $driver->get($testUrl)->shouldBeCalledTimes(1);
        $driver->getCurrentURL()->willReturn($testUrl)->shouldBeCalledTimes(1);
        $driver->getTitle()->willReturn('Test Page')->shouldBeCalledTimes(1);
        $webDriver->setDriver($driver->reveal());

        $webDriver->get($testUrl);
    }

    public function testWebDriver_deleteAllCookies()
    {
        $webDriver = new WebDriver($this->consoleOutput, new FirefoxProfile());

        $driver = $this->prophesize(\Facebook\WebDriver\WebDriver::class);
        $driver->quit()->shouldBeCalledTimes(1);

        $manage = $this->prophesize(WebDriverOptions::class);
        $manage->deleteAllCookies()->shouldBeCalledTimes(1);

        $driver->manage()->willReturn($manage->reveal());

        $webDriver->setDriver($driver->reveal());
        $webDriver->deleteAllCookies();
    }

    public function testWebDriver_getCookies()
    {
        $webDriver = new WebDriver($this->consoleOutput, new FirefoxProfile());

        $driver = $this->prophesize(\Facebook\WebDriver\WebDriver::class);
        $driver->quit()->shouldBeCalledTimes(1);

        $manage = $this->prophesize(WebDriverOptions::class);
        $manage->getCookies()->shouldBeCalledTimes(1);

        $driver->manage()->willReturn($manage->reveal());

        $webDriver->setDriver($driver->reveal());
        $webDriver->getCookies();
    }

    public function testWebDriver_getPageSource()
    {
        $webDriver = new WebDriver($this->consoleOutput, new FirefoxProfile());

        $driver = $this->prophesize(\Facebook\WebDriver\WebDriver::class);
        $driver->quit()->shouldBeCalledTimes(1);

        $driver->getPageSource()->shouldBeCalledTimes(1);

        $webDriver->setDriver($driver->reveal());
        $webDriver->getPageSource();
    }

    public function testWebDriver_waitForElement()
    {
        $webDriver = new WebDriver($this->consoleOutput, new FirefoxProfile());

        $driver = $this->prophesize(\Facebook\WebDriver\WebDriver::class);
        $driver->quit()->shouldBeCalledTimes(1);

        $wait = $this->prophesize(WebDriverWait::class);
        $wait->until(Argument::any())->shouldBeCalledTimes(3);

        $driver->wait(20)->shouldBeCalledTimes(1)->willReturn($wait->reveal());
        $driver->wait(10)->shouldBeCalledTimes(1)->willReturn($wait->reveal());
        $driver->wait(30)->shouldBeCalledTimes(1)->willThrow(\Exception::class);
        $driver->wait(40)->shouldBeCalledTimes(1)->willReturn($wait->reveal());

        $driver->findElements(Argument::any())->shouldBeCalledTimes(3)->willReturn(
            [],
            ['foo', 'bar', 'tree'],
            ['foo', 'bar', 'tree', 'last']
        );

        $webDriver->setDriver($driver->reveal());

        $this->assertFalse($webDriver->waitForElement(WebDriverBy::cssSelector('.test'), 0, 20));
        $this->assertEquals('bar', $webDriver->waitForElement(WebDriverBy::cssSelector('.test'), 1));
        $this->assertFalse($webDriver->waitForElement(WebDriverBy::cssSelector('.test'), 0, 30));
        $this->assertEquals('last', $webDriver->waitForElement(WebDriverBy::cssSelector('.test'), 6, 40));

    }

    public function testWebDriver_getAllElements()
    {
        $items = ['foo', 'bar', 'tree', 'last'];

        $webDriver = new WebDriver($this->consoleOutput, new FirefoxProfile());

        $driver = $this->prophesize(\Facebook\WebDriver\WebDriver::class);
        $driver->quit()->shouldBeCalledTimes(1);

        $wait = $this->prophesize(WebDriverWait::class);
        $wait->until(Argument::any())->shouldBeCalledTimes(1);

        $driver->wait(20)->shouldBeCalledTimes(1)->willReturn($wait->reveal());
        $driver->wait(30)->shouldBeCalledTimes(1)->willThrow(\Exception::class);

        $driver->findElements(Argument::any())->shouldBeCalledTimes(1)->willReturn($items);

        $webDriver->setDriver($driver->reveal());

        $this->assertEquals($items, $webDriver->getAllElements(WebDriverBy::cssSelector('.test'), 0, 20));
        $this->assertEquals([], $webDriver->getAllElements(WebDriverBy::cssSelector('.test'), 0, 30));

    }


    public function testWebDriver_checkForElement()
    {
        $webDriver = new WebDriver($this->consoleOutput, new FirefoxProfile());

        $driver = $this->prophesize(\Facebook\WebDriver\WebDriver::class);
        $driver->quit()->shouldBeCalledTimes(1);

        $driver->findElements(Argument::any())->shouldBeCalledTimes(2)->willReturn(['foo', 'bar', 'tree'], []);

        $webDriver->setDriver($driver->reveal());

        $this->assertEquals('foo', $webDriver->checkForElement(WebDriverBy::cssSelector('.test')));
        $this->assertFalse($webDriver->checkForElement(WebDriverBy::cssSelector('.test')));
    }

    public function testWebDriver_takeScreenshot()
    {
        $webDriver = new WebDriver($this->consoleOutput, new FirefoxProfile());

        $driver = $this->prophesize(\Facebook\WebDriver\WebDriver::class);
        $driver->quit()->shouldBeCalledTimes(1);

        $driver->takeScreenshot(null)->shouldBeCalledTimes(1);
        $driver->takeScreenshot('/tmp/tmp')->shouldBeCalledTimes(1);

        $webDriver->setDriver($driver->reveal());

        $webDriver->takeScreenshot();
        $webDriver->takeScreenshot('/tmp/tmp');
    }

    public function testWebDriver_executeScript()
    {
        $webDriver = new WebDriver($this->consoleOutput, new FirefoxProfile());

        $driver = $this->prophesize(\Facebook\WebDriver\WebDriver::class);
        $driver->willImplement(JavaScriptExecutor::class);
        $driver->quit()->shouldBeCalledTimes(1);

        $driver->executeAsyncScript('alert(1+1);', [])->shouldBeCalledTimes(1);
        $driver->executeScript('alert(1+1);', [])->shouldBeCalledTimes(1);

        $webDriver->setDriver($driver->reveal());

        $webDriver->executeScript('alert(1+1);', []);
        $webDriver->executeScript('alert(1+1);', [], true);
    }

    public function testWebDriver_getDriver()
    {
        $webDriver = new WebDriver($this->consoleOutput, new FirefoxProfile());

        $driver = $this->prophesize(\Facebook\WebDriver\WebDriver::class);
        $driver->quit()->shouldBeCalledTimes(1);

        $webDriver->setDriver($driver->reveal());

        $this->assertEquals($driver->reveal(), $webDriver->getDriver());
    }

    public function testWebDriver_switchToFrame()
    {
        $webDriver = new WebDriver($this->consoleOutput, new FirefoxProfile());

        $driver = $this->prophesize(\Facebook\WebDriver\WebDriver::class);
        $driver->quit()->shouldBeCalledTimes(1);

        $switcher = $this->prophesize(WebDriverTargetLocator::class);
        $switcher->frame(Argument::any())->shouldBeCalledTimes(1);
        $switcher->defaultContent()->shouldBeCalledTimes(1);

        $driver->switchTo()->shouldBeCalledTimes(2)->willReturn($switcher->reveal());

        $webDriver->setDriver($driver->reveal());

        $webDriver->switchToFrame('content');
        $webDriver->switchToDefaultContent();
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testWebDriver_setUpPreferences()
    {
        $ffProfile = new FirefoxProfile();
        $webDriver = new WebDriver($this->consoleOutput, $ffProfile);

        /** @var FirefoxProfile $profile */
        $profile = $this->invokeMethod($webDriver, 'setUpPreferences', [$ffProfile]);
        $this->assertEquals('"ru-RU,ru,en,en-US,uk"', $profile->getPreference('intl.accept_languages'));

        $profile = $this->invokeMethod($webDriver, 'setUpPreferences', [$ffProfile, ['lang' => 'en']]);
        $this->assertEquals('"en,en-us,ru-RU,ru,uk"', $profile->getPreference('intl.accept_languages'));

        $profile = $this
            ->invokeMethod($webDriver, 'setUpPreferences', [$ffProfile, ['accept_languages' => 'en,en-us']]);
        $this->assertEquals('"en,en-us"', $profile->getPreference('intl.accept_languages'));

        $profile = $this->invokeMethod($webDriver, 'setUpPreferences', [$ffProfile, ['no-flash' => true]]);
        $this->assertEquals('0', $profile->getPreference('plugin.state.flash'));
    }

    public function testWebDriver_setUpCapabilities()
    {
        $ffProfile = new FirefoxProfile();
        $webDriver = new WebDriver($this->consoleOutput, $ffProfile);

        /** @var WebDriverCapabilities $firefoxCapabilities */
        $firefoxCapabilities = $this
            ->invokeMethod($webDriver, 'setUpCapabilities', [$ffProfile, ['proxy' => '127.0.0.1:8080']]);

        $this->assertEquals('firefox', $firefoxCapabilities->getBrowserName());
        $this->assertEquals('ANY', $firefoxCapabilities->getPlatform());
        $this->assertArraySubset(
            [
                'proxyType' => 'manual',
                'httpProxy' => '127.0.0.1:8080',
                'sslProxy' => '127.0.0.1:8080',

            ],
            $firefoxCapabilities->getCapability('proxy')
        );
    }
}
