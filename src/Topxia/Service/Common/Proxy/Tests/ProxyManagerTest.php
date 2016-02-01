<?php

namespace Topxia\Service\Common\Proxy\Test;

use Topxia\Service\Common\Proxy\ProxyManager;
use Topxia\Service\Common\BaseTestCase;

class ProxyManagerTest extends BaseTestCase
{
    public function testCreate()
    {
        $this->getUserService()->getBlacklist(1);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.BlacklistService');
    }
}