<?php

namespace Tests\Functional\Tests;

use Tests\Functional\Page\Web\HomePage;

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
