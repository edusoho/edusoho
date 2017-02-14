<?php

namespace EduSoho\AT\Page\Web;

use EduSoho\AT\Page\Page;

class LoginPage extends Page
{
    public function login($username, $password)
    {
        $usernameEle = $this->driver->findElement(\WebDriverBy::name('_username'));
        $this->driver->getMouse()->click($usernameEle->getCoordinates());
        $this->driver->getKeyboard()->sendKeys($username);
       
        $passwordEle = $this->driver->findElement(\WebDriverBy::name('_username'));
        $this->driver->getMouse()->click($passwordEle->getCoordinates());
        $this->driver->getKeyboard()->sendKeys($password);



        $element = $this->driver->findElement(\WebDriverBy::xpath('//button[@class="btn btn-primary btn-lg btn-block"]'));
        $element->submit();
        $this->driver->getMouse()->click($element->getCoordinates());
    }
}
