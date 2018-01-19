<?php

namespace Tests\Unit\CloudPlatform;

use Biz\BaseTestCase;
use Biz\CloudPlatform\IMAPIFactory;
use Biz\CloudPlatform\Service\PushService;

class PushServiceTest extends BaseTestCase
{
    public function testPush()
    {
        $api = IMAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('success' => true));
        $this->getPushService()->setImApi($api);

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'withParams' => array('app_im', array()),
                'returnValue' => array('enabled' => 1, 'convNo' => 123),
            ),
            array(
                'functionName' => 'get',
                'withParams' => array('developer', array()),
                'returnValue' => array('debug' => 1),
            ),
        ));

        $from = array(
            'id' => 1,
            'type' => 'user',
        );

        $to = array(
            'type' => 'user',
            'id' => 2,
            'convNo' => 'test convNo',
        );

        $body = array(
            'type' => 'user.follow',
            'fromId' => 1,
            'toId' => 2,
            'title' => '收到一个用户关注',
            'message' => '{Test User}已经关注了你！',
        );

        $result = $this->getPushService()->push($from, $to, $body);

        $this->assertNull($result);
    }

    /**
     * @return PushService
     */
    protected function getPushService()
    {
        return $this->createService('CloudPlatform:PushService');
    }
}
