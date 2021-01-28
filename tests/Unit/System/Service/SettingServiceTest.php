<?php

namespace Tests\Unit\System\Service;

use Biz\BaseTestCase;

class SettingServiceTest extends BaseTestCase
{
    public function testNode()
    {
        $this->getSettingService()->set('site', array('name' => 'edusoho', 'url' => 'www.edusoho.com'));
        $result = $this->getSettingService()->node('site.name');
        $this->assertEquals('edusoho', $result);
    }

    public function testGet()
    {
        $this->getSettingService()->set('site', array('name' => 'edusoho', 'url' => 'www.edusoho.com'));
        $foundSetting = $this->getSettingService()->get('site');
        $this->assertEquals('edusoho', $foundSetting['name']);
        $this->assertEquals('www.edusoho.com', $foundSetting['url']);

        $this->getSettingService()->set('site', array('name' => 'baidu', 'url' => 'www.baidu.com'));
        $foundSetting = $this->getSettingService()->get('site');
        $this->assertEquals('baidu', $foundSetting['name']);
        $this->assertEquals('www.baidu.com', $foundSetting['url']);
    }

    public function testDelete()
    {
        $this->getSettingService()->set('site', array('name' => 'edusoho', 'url' => 'www.edusoho.com'));
        $foundSetting = $this->getSettingService()->get('site');
        $this->assertEquals('edusoho', $foundSetting['name']);
        $this->assertEquals('www.edusoho.com', $foundSetting['url']);

        $this->getSettingService()->delete('site');
        $foundSetting = $this->getSettingService()->get('site');

        $this->assertEquals(0, count($foundSetting));
    }

    public function testSetByNamespace()
    {
        $this->getSettingService()->setByNamespace('/Biz', 'Setting', '123');
    }

    public function testIsReservationOpen()
    {
        $this->assertFalse($this->getSettingService()->isReservationOpen());
        $this->getSettingService()->set('plugin_reservation', array('reservation_enabled' => 1));
        $this->assertTrue($this->getSettingService()->isReservationOpen());
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
