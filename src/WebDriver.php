<?php namespace LionMM\WebDriver;

use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Firefox\FirefoxDriver;
use Facebook\WebDriver\Firefox\FirefoxProfile;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriver AS OriginalWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverCapabilities;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverHasInputDevices;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class WebDriver
 * @package LionMM\WebDriver
 */
class WebDriver
{
    const DEFAULT_WAIT_TIMEOUT = 10;

    /** @var ConsoleOutput */
    private $output;

    /**  @var OriginalWebDriver|JavaScriptExecutor|WebDriverHasInputDevices */
    private $driver;

    /** @var FirefoxProfile */
    private $firefoxProfile;

    /**
     * WebDriver constructor.
     * @param ConsoleOutput $consoleOutput
     * @param FirefoxProfile $firefoxProfile
     */
    public function __construct(ConsoleOutput $consoleOutput, FirefoxProfile $firefoxProfile)
    {
        $this->output = $consoleOutput;
        $this->firefoxProfile = $firefoxProfile;
    }

    public function __destruct()
    {
        if ($this->driver) {
            $this->quit();
        }
    }

    /**
     * @param array $configuration
     * @param int $request_time_limit
     * @param string $webdriverUrl
     * @return WebDriver
     */
    public function initDriver(
        array $configuration = [],
        $request_time_limit = 50000,
        $webdriverUrl = 'http://localhost:4444/wd/hub'
    ): self {
        if ($this->driver) {
            $this->output->writeln('<info>WebDriver instance already initialised</info>');

        } else {
            $this->output->writeln('<info>WebDriver instance initialising</info>');

            $profile = $this->setUpPreferences($this->firefoxProfile, $configuration);

            $firefoxCapabilities = $this->setUpCapabilities($profile, $configuration);

            $driver = $this->createDriverInstance($webdriverUrl, $firefoxCapabilities, $request_time_limit);
            $this->setDriver($driver);
        }

        return $this;
    }

    /**
     * @param $webdriverUrl
     * @param $firefoxCapabilities
     * @param $request_time_limit
     * @return RemoteWebDriver
     */
    private function createDriverInstance($webdriverUrl, $firefoxCapabilities, $request_time_limit): RemoteWebDriver
    {
        return RemoteWebDriver::create($webdriverUrl, $firefoxCapabilities, 5000, $request_time_limit);
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
    private function setUpPreferences(FirefoxProfile $profile, array $preferences = []): FirefoxProfile
    {
        $accept_languages = 'ru-RU,ru,en,en-US,uk';
        if ($preferences['accept_languages'] ?? false) {
            $accept_languages = $preferences['accept_languages'];
        } elseif (($preferences['lang'] ?? false) === 'en') {
            $accept_languages = 'en,en-us,ru-RU,ru,uk';
        }

        $profile->setPreference('intl.accept_languages', $accept_languages);

        if ($preferences['no-flash'] ?? false) {
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
    private function setUpCapabilities($profile, array $capabilities = []): WebDriverCapabilities
    {
        $firefoxCapabilities = DesiredCapabilities::firefox();
        $firefoxCapabilities->setCapability(FirefoxDriver::PROFILE, $profile);
        if ($capabilities && isset($capabilities['proxy'])) {
            $firefoxCapabilities->setCapability(WebDriverCapabilityType::PROXY, [
                'proxyType' => 'manual',
                'httpProxy' => $capabilities['proxy'],
                'sslProxy' => $capabilities['proxy'],
            ]);
        }

        return $firefoxCapabilities;
    }

    /**
     * Quits driver, closing every associated window.
     *
     * @param int $sec
     */
    public function quit($sec = 0): void
    {
        if ($this->driver) {
            sleep($sec);
            $this->driver->quit();
            $this->driver = null;
            $this->output->writeln('<info>WebDriver instance unloaded</info>');
        }
    }

    /**
     * Load a new web page in the current browser window.
     *
     * @param string $url
     * @return self The current instance.
     */
    public function get($url): self
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
     * @return self The current instance.
     */
    public function deleteAllCookies(): self
    {
        $this->driver->manage()->deleteAllCookies();
        $this->output->writeln('    <info>Cookies deleted</info>');

        return $this;
    }

    /**
     * Get all the cookies for the current domain.
     *
     * @return Cookie[] The array of cookies presented.
     */
    public function getCookies(): array
    {
        if (!$this->driver->manage()) {
            return [];
        }

        return $this->driver->manage()->getCookies();
    }


    /**
     * Add a specific cookie
     *
     * @param Cookie|array $cookie Cookie object. May be also created from array for compatibility reasons.
     * @return self
     */
    public function addCookie($cookie): self
    {
        $this->driver->manage()->addCookie($cookie);

        return $this;
    }


    /**
     *
     *
     * @param string|null $pathToSave
     * @return string JSON stringify cookies
     */
    public function saveSerializedCookies($pathToSave = null): string
    {
        $cookiesArray = [];
        $cookiesObjectsArray = $this->getCookies();
        if ($cookiesObjectsArray) {
            foreach ($cookiesObjectsArray as $cookieObject) {
                $cookiesArray[] = $cookieObject->toArray();
            }
        }
        $cookiesJson = json_encode($cookiesArray);

        if ($pathToSave) {
            @file_put_contents($pathToSave, $cookiesJson);
        }

        return $cookiesJson;
    }

    /**
     *
     *
     * @param array|string $cookiesSource Path to file or array of cookies data
     * @param string $openUrl Set up cookies is possible only when the page is open,
     * @return bool
     */
    public function restoreSerializedCookies($cookiesSource, string $openUrl = null): bool
    {
        if (is_string($cookiesSource)) {
            $cookiesJson = @file_get_contents($cookiesSource);
            $cookiesSource = json_decode($cookiesJson ?? '', true);
        }

        if (is_array($cookiesSource)) {
            if ($openUrl) {
                $this->get($openUrl);
            }
            foreach ($cookiesSource as $cookieArray) {
                $this->addCookie($cookieArray);
            }

            return true;
        }

        return false;
    }


    /**
     * Get a string representing the current URL that the browser is looking at.
     *
     * @return string The current URL.
     */
    public function getCurrentURL(): string
    {
        return $this->driver->getCurrentURL();
    }

    /**
     * Get the source of the last loaded page.
     *
     * @return string The current page source.
     */
    public function getPageSource(): string
    {
        return $this->driver->getPageSource();
    }

    /**
     * Get the title of the current page.
     *
     * @return string The title of the current page.
     */
    public function getTitle(): string
    {
        return $this->driver->getTitle();
    }


    /**
     * @param WebDriverBy $selector
     * @param int $index
     * @param int $timeout
     * @return WebDriverElement|null
     */
    public function waitForElement(WebDriverBy $selector, $index = 0, $timeout = 0): ?WebDriverElement
    {
        $need = $index + 1;
        $wait = $timeout ?: self::DEFAULT_WAIT_TIMEOUT;

        try {
            $this->driver
                ->wait($wait)
                ->until(WebDriverExpectedCondition::presenceOfAllElementsLocatedBy($selector, $need));
        } catch (\Exception $e) {
            return null;
        }

        $elements = $this->driver->findElements($selector);
        if (count($elements)) {
            return ($elements[$index] ?? array_pop($elements));
        }

        return null;
    }

    /**
     * @param WebDriverBy $selector
     * @param int $need
     * @param int $timeout
     * @return WebDriverElement[]
     */
    public function getAllElements(WebDriverBy $selector, $need = 0, $timeout = 0): array
    {
        $need = $need ?: 1;
        $wait = $timeout ?: self::DEFAULT_WAIT_TIMEOUT;

        try {
            $this->driver
                ->wait($wait)
                ->until(WebDriverExpectedCondition::presenceOfAllElementsLocatedBy($selector, $need));
        } catch (\Exception $e) {
            return [];
        }

        return $this->driver->findElements($selector);
    }


    /**
     * @param WebDriverBy $selector
     * @return WebDriverElement
     */
    public function checkForElement(WebDriverBy $selector): ?WebDriverElement
    {
        $elements = $this->driver->findElements($selector);

        return count($elements) > 0 ? array_shift($elements) : null;
    }

    /**
     * Switch to the iframe by its id or name.
     *
     * @param $element WebDriverElement|string $frame The WebDriverElement, the id or the name of the frame.
     * @return self The driver focused on the given frame.
     */
    public function switchToFrame($element): self
    {
        $this->driver->switchTo()->frame($element);

        return $this;
    }

    /**
     * Switch to the main document if the page contains iframes. Otherwise, switch
     * to the first frame on the page.
     *
     * @return self The driver focused on the top window or the first frame.
     */
    public function switchToDefaultContent(): self
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
    public function takeScreenshot($save_as = null): string
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

    /**
     * @return OriginalWebDriver|JavaScriptExecutor|WebDriverHasInputDevices
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param OriginalWebDriver $driver
     * @return self
     */
    public function setDriver(OriginalWebDriver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }
}
