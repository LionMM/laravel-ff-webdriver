<?php namespace LionMM\WebDriver;

use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Firefox\FirefoxDriver;
use Facebook\WebDriver\Firefox\FirefoxProfile;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverCapabilities;
use Facebook\WebDriver\WebDriverElement;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class WebDriver
 * @package LionMM\WebDriver
 */
class WebDriver
{
    /**
     * @var int
     */
    private static $wait_delay = 6;
    /**
     * @var null|ConsoleOutput
     */
    private $output = null;
    /**
     * @var \Facebook\WebDriver\WebDriver|JavaScriptExecutor
     */
    private $driver = null;

    /**
     * WebDriver constructor.
     * @param ConsoleOutput $consoleOutput
     */
    public function __construct(ConsoleOutput $consoleOutput)
    {
        $this->output = $consoleOutput;
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->driver) {
            $this->quit();
            $this->output->writeln('<info>WebDriver instance destructing</info>');
        }
    }

    /**
     * @param array $configuration
     * @param int $request_time_limit
     * @throws WebDriverException
     */
    public function initDriver(
        $configuration = [],
        $request_time_limit = 50000
    ) {
        if ($this->driver) {
            $this->output->writeln('<info>WebDriver instance already initialised</info>');

        } else {
            $this->output->writeln('<info>WebDriver instance initialising</info>');


            $profile = $this->setUpPreferences(new FirefoxProfile(), $configuration);


            $firefoxCapabilities = $this->setUpCapabilities($profile, $configuration);

            $url = config('webdriver.host') . ':' . config('webdriver.port') . config('webdriver.path');
            $this->driver = RemoteWebDriver::create($url, $firefoxCapabilities, 5000, $request_time_limit);

        }
    }

    /**
     * Set up Firefox preferences
     * Supporting keys: accept_languages OR lang (string), no-flash (boolean)
     *
     * @param FirefoxProfile $profile
     * @param array $preferences
     * @return FirefoxProfile
     * @throws WebDriverException
     */
    private function setUpPreferences(FirefoxProfile $profile, $preferences)
    {
        $accept_languages = 'ru-RU,ru,en,en-US,uk';
        if (array_get($preferences, 'accept_languages')) {
            $accept_languages = array_get($preferences, 'accept_languages');
        } elseif (array_get($preferences, 'lang') === 'en') {
            $accept_languages = 'en,en-us,ru-RU,ru,uk';
        }

        $profile->setPreference('intl.accept_languages', $accept_languages);

        if (array_get($preferences, 'no-flash')) {
            $profile->setPreference('plugin.state.flash', 0);
            $profile->setPreference('dom.ipc.plugins.enabled.libflashplayer.so', false);
            $profile->setPreference('dom.ipc.plugins.flash.subprocess.crashreporter.enabled', false);
        }

        return $profile;
    }

    /**
     * @param $profile
     * @param array $capabilities
     * @return WebDriverCapabilities
     */
    private function setUpCapabilities($profile, $capabilities)
    {
        $firefoxCapabilities = DesiredCapabilities::firefox();
        $firefoxCapabilities->setCapability(FirefoxDriver::PROFILE, $profile);
        if ($capabilities) {
            if (array_get($capabilities, 'proxy', false)) {
                $firefoxCapabilities->setCapability(WebDriverCapabilityType::PROXY, [
                    'proxyType' => 'manual',
                    'httpProxy' => array_get($capabilities, 'proxy'),
                    'sslProxy' => array_get($capabilities, 'proxy'),
                ]);
            }
        }
        return $firefoxCapabilities;
    }

    /**
     * @param int $sec
     */
    public function quit($sec = 0)
    {
        if ($this->driver) {
            sleep($sec);
            $this->driver->quit();
            $this->driver = null;
        }
    }

    /**
     * @param string $url
     * @return string
     */
    public function get($url)
    {
        $this->output->writeln('    <info>open page: ' . $url . '</info>');

        $this->driver->get($url);

        $this->output->writeln('    <info>URL opened: ' . $this->driver->getCurrentURL() . '</info>');
        $this->output->writeln(
            '    <info>Window title:</info> <comment>'
            . $this->driver->getTitle()
            . '</comment>'
        );

        return $this->driver->getCurrentURL();
    }

    /**
     *
     */
    public function deleteAllCookies()
    {
        $this->driver->manage()->deleteAllCookies();
        $this->output->writeln('    <info>Cookies deleted</info>');
    }


    /**
     * @return string
     */
    public function getCurrentURL()
    {
        return $this->driver->getCurrentURL();
    }


    /**
     * @param WebDriverBy $selector
     * @param int $index
     * @param int $timeout
     * @return bool|WebDriverElement
     */
    public function waitForElement(WebDriverBy $selector, $index = 0, $timeout = 0)
    {
        $elements_count = 0;
        $need = $index + 1;

        $wait = $timeout ?: self::$wait_delay;

        $delay_start_time = time();
        while ($elements_count < $need) {
            $elements = $this->driver->findElements($selector);
            $elements_count = count($elements);

            if ((time() - $delay_start_time) >= $wait) {
                break;
            }
            sleep(1);
        }

        if ($elements_count) {
            return (isset($elements[$index]) ? $elements[$index] : array_shift($elements));
        }

        return false;
    }

    /**
     * @param WebDriverBy $selector
     * @param int $need
     * @param int $timeout
     * @return array
     */
    public function getAllElements(WebDriverBy $selector, $need = 0, $timeout = 0)
    {
        $elements_count = 0;
        $need = $need ?: $need + 1;

        $wait = $timeout ?: self::$wait_delay;

        $delay_start_time = time();
        $elements = [];

        while ($elements_count < $need) {
            $elements = $this->driver->findElements($selector);
            $elements_count = count($elements);

            if ((time() - $delay_start_time) >= $wait) {
                break;
            }
        }

        return $elements;
    }


    /**
     * @param WebDriverBy $selector
     * @return bool|WebDriverElement
     */
    public function checkForElement(WebDriverBy $selector)
    {
        $elements = $this->driver->findElements($selector);

        return count($elements) > 0 ? array_shift($elements) : false;
    }

    /**
     * @param WebDriverElement $element
     */
    public function switchToFrame(WebDriverElement $element)
    {
        $this->driver->switchTo()->frame($element);
    }

    /**
     *
     */
    public function switchToDefaultContent()
    {
        $this->driver->switchTo()->defaultContent();
    }

    /**
     * @param string|null $save_as
     */
    public function takeScreenshot($save_as = null)
    {
        $this->driver->takeScreenshot($save_as);
    }

    /**
     * @param string $js
     * @return mixed
     */
    public function executeScript($js)
    {
        return $this->driver->executeScript($js);
    }
}
