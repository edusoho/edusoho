<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Upgrade;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class UpgradeServiceTest extends BaseTestCase
{

    public function testAssertNull()
    {
        $this->assertNull(NULL);
    }

    private function getUpgradeService()
    {
        return $this->getServiceKernel()->createService('Upgrade.UpgradeService');
    }

}