<?php

namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\User\AuthService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

// TODO

class AuthServiceTest extends BaseTestCase
{
    private function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }
}