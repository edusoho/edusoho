<?php

namespace Tests\Functional\Page\Web;

use Tests\Functional\Page\Page;
use Facebook\WebDriver\WebDriverBy;

class HomePage extends Page
{
    public function isShowUserAvatarVisible()
    {
        try {
            $element = $this->driver->findElement(WebDriverBy::xpath('//li[@class="user-avatar-li nav-hover"]'));
        } catch (\Exception $exception) {
            return false;
        }

        return $element->isDisplayed();
    }

    /**
     * Open the home page.
     */
    public function open()
    {
        $this->driver->get(getenv('TEST_SITE_HOST'));
    }

    /**
     * Click on the "Login" btn.
     *
     * @return LoginPage
     */
    public function clickLogin()
    {
        /** @var \WebDriverLocatable|\WebDriverElement $element */
        $element = $this->driver->findElement(WebDriverBy::xpath('//li[@class="hidden-xs"]//a[@href="/login?goto=/"]'));
        $element->click();

        return new LoginPage($this->driver);
    }
}
