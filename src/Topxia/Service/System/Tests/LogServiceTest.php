<?php
namespace Topxia\Service\System\Tests;

use Topxia\Service\Common\BaseTestCase;

// TODO

class LogServiceTest extends BaseTestCase
{   

    public function testLogXXX()
    {
       $this->assertNull(null);
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

}