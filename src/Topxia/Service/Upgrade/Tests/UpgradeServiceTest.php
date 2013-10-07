<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Upgrade;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class UpgradeServiceTest extends BaseTestCase
{
    public function setUp()
    {
    	parent::setUp();
  		$_SERVER['SERVER_ADDR'] = '127.0.0.1';
		$_SERVER['SERVER_NAME']  = 'www.edusoho.com';	
    }


    public function testTest()
    {
        $this->getUpgradeService()->check();
    }

    private function getUpgradeService()
    {
        return $this->getServiceKernel()->createService('Upgrade.UpgradeService');
    }

}