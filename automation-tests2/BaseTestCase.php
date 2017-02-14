<?php

namespace EduSoho\AT;

use Facebook\WebDriver\Remote\RemoteWebdriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Remote\DesiredCapabilities;

class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    protected $driver;

    public function setUp()
    {
        $this->driver = RemoteWebDriver::create(
            getenv('HUB_URL'),
            DesiredCapabilities::firefox()
        );
    }

    protected function tearDown()
    {
        //$this->driver->quit();
    }
}
