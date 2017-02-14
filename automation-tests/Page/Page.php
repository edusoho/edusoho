<?php

namespace EduSoho\AT\Page;

use Facebook\WebDriver\Remote\RemoteWebdriver;

abstract class Page
{
    /** @var RemoteWebDriver */
    protected $driver;

    /**
     * @param RemoteWebDriver $driver
     */
    public function __construct(RemoteWebDriver $driver)
    {
        $this->driver = $driver;
    }
}
