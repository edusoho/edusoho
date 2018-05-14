<?php

namespace Tests\Unit\System\Service;

use Biz\BaseTestCase;

class SettingServiceTest extends BaseTestCase
{
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

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
