<?php

namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\User\AuthService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

// TODO

class AuthServiceTest extends BaseTestCase
{

    public function testAuthXXX()
    {
       $this->assertNull(null);
    }

    private function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }
}