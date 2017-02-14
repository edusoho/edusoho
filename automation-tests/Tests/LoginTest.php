<?php

namespace EduSoho\AT\Tests;

use EduSoho\AT\BaseTestCase;
use EduSoho\AT\Page\Web\HomePage;

class LoginTest extends BaseTestCase
{
    public function testLoginWithSuccess()
    {
        $homepage = new HomePage($this->driver);
        $homepage->open();
        $loginPage = $homepage->clickLogin();
        $loginPage->login('test@edusoho.com', 'kaifazhe');

        $this->assertTrue($homepage->isShowUserAvatarVisible());
    }
}
