<?php

namespace Tests\Functional\Tests;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    protected $driver;

    public function setUp()
    {
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
