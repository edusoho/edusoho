<?php
namespace Topxia\Service\System\Tests;

use Topxia\Service\Common\BaseTestCase;

// TODO

class LogServiceTest extends BaseTestCase
{   

    private function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

}