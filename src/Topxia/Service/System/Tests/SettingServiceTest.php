<?php
namespace Topxia\Service\System\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\System\SettingService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class SettingServiceTest extends BaseTestCase
{   

    public function testGet()
    {
        $this->getSettingService()->set('site', array('name'=>'edusoho','url'=>'www.edusoho.com'));
        $foundSetting = $this->getSettingService()->get('site');
        $this->assertEquals('edusoho', $foundSetting['name']);
        $this->assertEquals('www.edusoho.com', $foundSetting['url']);

        $this->getSettingService()->set('site', array('name'=>'baidu','url'=>'www.baidu.com'));
        $foundSetting = $this->getSettingService()->get('site');
        $this->assertEquals('baidu', $foundSetting['name']);
        $this->assertEquals('www.baidu.com', $foundSetting['url']);
    }

    public function testDelete()
    {
        $this->getSettingService()->set('site', array('name'=>'edusoho','url'=>'www.edusoho.com'));
        $this->getSettingService()->delete('xxx');
        $foundSetting = $this->getSettingService()->get('site');
        $this->assertEquals('edusoho', $foundSetting['name']);
        $this->assertEquals('www.edusoho.com', $foundSetting['url']);

        $this->getSettingService()->delete('site');
        $foundSetting = $this->getSettingService()->get('site');
        $this->assertNull($foundSetting);
    }
    
    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}