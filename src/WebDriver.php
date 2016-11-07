<?php namespace LionMM\WebDriver;

use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Firefox\FirefoxDriver;
use Facebook\WebDriver\Firefox\FirefoxProfile;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\DriverCommand;
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
    const DEFAULT_WAIT_TIMEOUT = 6;
    /**
     * @var null|ConsoleOutput
     */
    private $output = null;
    /**
     * @var \Facebook\WebDriver\WebDriver|JavaScriptExecutor
     */
    private $driver = null;

    /**
     * @var FirefoxProfile
     */
    private $ffProfile;

    /**
     * WebDriver constructor.
     * @param ConsoleOutput $consoleOutput
     * @param FirefoxProfile $firefoxProfile
     */
    public function __construct(ConsoleOutput $consoleOutput, FirefoxProfile $firefoxProfile)
    {
        $this->output = $consoleOutput;
        $this->ffProfile = $firefoxProfile;
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
     * @return $this
     * @throws WebDriverException
     */
    public function initDriver(
        array $configuration = [],
        $request_time_limit = 50000
    ) {
        if ($this->driver) {
            $this->output->writeln('<info>WebDriver instance already initialised</info>');

        } else {
            $this->output->writeln('<info>WebDriver instance initialising</info>');


            $profile = $this->setUpPreferences($this->ffProfile, $configuration);


            $firefoxCapabilities = $this->setUpCapabilities($profile, $configuration);

            $url = config('webdriver.host') . ':' . config('webdriver.port') . config('webdriver.path');
            $this->driver = RemoteWebDriver::create($url, $firefoxCapabilities, 5000, $request_time_limit);
        }

        return $this;
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
    private function setUpPreferences(FirefoxProfile $profile, array $preferences = [])
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
    private function setUpCapabilities($profile, array $capabilities = [])
    {
        $firefoxCapabilities = DesiredCapabilities::firefox();
        $firefoxCapabilities->setCapability(FirefoxDriver::PROFILE, $profile);
        if ($capabilities && array_get($capabilities, 'proxy', false)) {
            $firefoxCapabilities->setCapability(WebDriverCapabilityType::PROXY, [
                'proxyType' => 'manual',
                'httpProxy' => array_get($capabilities, 'proxy'),
                'sslProxy' => array_get($capabilities, 'proxy'),
            ]);
        }

        return $firefoxCapabilities;
    }

    /**
     * Quits driver, closing every associated window.
     * @param int $sec
     */
    public function quit($sec = 0)
    {
        if ($this->driver) {
            sleep($sec);
            $this->driver->quit();
            unset($this->driver);
        }
    }

    /**
     * Load a new web page in the current browser window.
     *
     * @param string $url
     * @return $this The current instance.
     */
    public function get($url)
    {
        $this->output->writeln('    <info>opening page: ' . $url . '</info>');

        $this->driver->get($url);

        $this->output->writeln('    <info>URL opened: ' . $this->getCurrentURL() . '</info>');
        $this->output->writeln('    <info>Window title:</info> <comment>' . $this->getTitle() . '</comment>');

        return $this;
    }

    /**
     * Delete all the cookies that are currently visible.
     *
     * @return $this The current instance.
     */
    public function deleteAllCookies()
    {
        $this->driver->manage()->deleteAllCookies();
        $this->output->writeln('    <info>Cookies deleted</info>');

        return $this;
    }

    /**
     * Get all the cookies for the current domain.
     *
     * @return array The array of cookies presented.
     */
    public function getCookies()
    {
        return $this->driver->manage()->execute(DriverCommand::GET_ALL_COOKIES);
    }


    /**
     * Get a string representing the current URL that the browser is looking at.
     *
     * @return string The current URL.
     */
    public function getCurrentURL()
    {
        return $this->driver->getCurrentURL();
    }

    /**
     * Get the source of the last loaded page.
     *
     * @return string The current page source.
     */
    public function getPageSource()
    {
        return $this->driver->getPageSource();
    }

    /**
     * Get the title of the current page.
     *
     * @return string The title of the current page.
     */
    public function getTitle()
    {
        return $this->driver->getTitle();
    }


    /**
     * @param WebDriverBy $selector
     * @param int $index
     * @param int $timeout
     * @return bool|WebDriverElement
     */
    public function waitForElement(WebDriverBy $selector, $index = 0, $timeout = 0)
    {
        $need = $index + 1;
        $wait = $timeout ?: self::DEFAULT_WAIT_TIMEOUT;

        try {
            $this->driver
                ->wait($wait, 1000)
                ->until(WebDriverExpectedCondition::presenceOfAllElementsLocatedBy($selector, $need));
        } catch (\Exception $e) {
            return false;
        }

        $elements = $this->driver->findElements($selector);
        if (count($elements)) {
            return (isset($elements[$index]) ? $elements[$index] : array_pop($elements));
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
        $need = $need ?: 1;
        $wait = $timeout ?: self::DEFAULT_WAIT_TIMEOUT;

        try {
            $this->driver
                ->wait($wait, 1000)
                ->until(WebDriverExpectedCondition::presenceOfAllElementsLocatedBy($selector, $need));
        } catch (\Exception $e) {
            return [];
        }

        return $this->driver->findElements($selector);
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
     * Switch to the iframe by its id or name.
     *
     * @param $element WebDriverElement|string $frame The WebDriverElement, the id or the name of the frame.
     * @return $this The driver focused on the given frame.
     */
    public function switchToFrame($element)
    {
        $this->driver->switchTo()->frame($element);

        return $this;
    }

    /**
     * Switch to the main document if the page contains iframes. Otherwise, switch
     * to the first frame on the page.
     *
     * @return $this The driver focused on the top window or the first frame.
     */
    public function switchToDefaultContent()
    {
        $this->driver->switchTo()->defaultContent();

        return $this;
    }

    /**
     * Take a screenshot of the current page.
     *
     * @param string $save_as The path of the screenshot to be saved.
     * @return string The screenshot in PNG format.
     */
    public function takeScreenshot($save_as = null)
    {
        return $this->driver->takeScreenshot($save_as);
    }

    /**
     * Inject a snippet of JavaScript into the page for execution in the context
     * of the currently selected frame. The result of evaluating the script will be returned.
     *
     * @param string $script The script to inject.
     * @param array $arguments The arguments of the script.
     * @param bool $async
     * @return mixed The return value of the script.
     */
    public function executeScript($script, array $arguments = [], $async = false)
    {
        if ($async) {
            return $this->driver->executeAsyncScript($script, $arguments);
        }

        return $this->driver->executeScript($script, $arguments);
    }
}
