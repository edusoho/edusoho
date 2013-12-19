<?php

namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\User\DiskService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

// TODO

class DiskServiceTest extends BaseTestCase
{

    public function testDiskXXX()
    {
       $this->assertNull(null);
    }

    private function getDiskService()
    {
        return $this->getServiceKernel()->createService('User.DiskService');
    }

}