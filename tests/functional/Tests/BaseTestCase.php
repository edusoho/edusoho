<?php

namespace Tests\Functional\Tests;

use Facebook\WebDriver\Remote\RemoteWebdriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    protected $driver;

    public function setUp()
    {
        var_dump(file_exists('/builds/root/edusoho/vendor/facebook/webdriver/lib/Remote/RemoteWebDriver.php'), 1);
        var_dump(file_exists('/builds/root/edusoho/vendor'), 2);
        var_dump(file_exists('/builds/root/edusoho/vendor/facebook'), 3);
        var_dump(file_exists('/builds/root/edusoho/vendor/facebook/webdriver/lib'), 4);
        $browserName = getenv('BROWSER_NAME');
        $this->driver = RemoteWebDriver::create(
            getenv('SELENIUM_SERVER_URL'),
            DesiredCapabilities::$browserName()
        );
    }

    protected function tearDown()
    {
        $this->driver->quit();
    }
}
