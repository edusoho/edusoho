<?php

namespace Tests\Unit\System\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\System\Util\LogDataUtils;

class LogDataTest extends BaseTestCase
{
    public function testGetYmlConfig()
    {
        $result = LogDataUtils::getYmlConfig();

        $this->assertNotEmpty($result);
    }

    public function testGetConfigPath()
    {
        $result = ReflectionUtils::invokeMethod(new LogDataUtils(), 'getConfigPath', array());

        $this->assertNotEmpty($result);
    }

    public function testGetLogDefaultConfig()
    {
        $result = LogDataUtils::getLogDefaultConfig();

        $this->assertNotEmpty($result);
    }

    public function testGetUnDisplayModuleAction()
    {
        $result = LogDataUtils::getUnDisplayModuleAction();

        $this->assertNotEmpty($result);
    }

    public function testGetTransPrefix()
    {
        $result = LogDataUtils::getTransPrefix('testMessage', 'testModule');

        $this->assertNotEmpty($result);
    }

    public function testTrans()
    {
        $result = LogDataUtils::trans('testMessage', 'testModule', 'testAction');

        $this->assertNotEmpty($result);
    }

    public function testTimeConvent()
    {
        $result = ReflectionUtils::invokeMethod(new LogDataUtils(), 'timeConvent', array(time()));

        $this->assertNotEmpty($result);
    }

    public function testPasswordSetBlank()
    {
        $result = ReflectionUtils::invokeMethod(new LogDataUtils(), 'passwordSetBlank', array('XXXX'));

        $this->assertNotEmpty($result);
    }

    public function testGetShowTitleWithNotNickName()
    {
        $oldData = array('title' => 'test2', 'courseSetTitle' => 'test3', 'name' => 'test');
        $result = ReflectionUtils::invokeMethod(new LogDataUtils(), 'getShowTitle', array($oldData));

        $this->assertEquals('test3-test2', $result);
    }
}
