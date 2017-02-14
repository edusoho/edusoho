<?php

namespace EduSoho\AT\Page\Web;

use EduSoho\AT\Page\Page;
use EduSoho\AT\Page\Web\LoginPage;

class HomePage extends Page
{
    /**
     * Open the home page
     */
    public function open()
    {
        $this->driver->get(getenv('TEST_SITE_HOST'));
    }

    /**
     * Click on the "Login" btn
     *
     * @return LoginPage
     */
    public function clickLogin()
    {
        /** @var \WebDriverLocatable|\WebDriverElement $element */
        $element = $this->driver->findElement(\WebDriverBy::xpath('//a[@href="//login"]'));
        $this->driver->getMouse()->click($element->getCoordinates());
        return new LoginPage($this->driver);
    }
}
