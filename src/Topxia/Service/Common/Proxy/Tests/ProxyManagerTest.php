<?php

namespace Topxia\Service\Common\Proxy\Test;

use Topxia\Service\Common\Proxy\ProxyManager;
use Topxia\Service\Common\BaseTestCase;

class ProxyManagerTest extends BaseTestCase
{
    public function testCreate()
    {
        $this->getUserService();
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}