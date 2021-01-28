<?php

namespace Tests\Unit\CloudPlatform\QueueJob;

use Biz\BaseTestCase;
use Biz\CloudPlatform\IMAPIFactory;
use Biz\CloudPlatform\QueueJob\PushJob;
use Biz\CloudPlatform\Service\PushService;

class PushJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $context = array(
            'body' => array(
                'type' => 'user.follow',
                'fromId' => 1,
                'toId' => 2,
                'title' => '收到一个用户关注',
                'message' => '{Test User}已经关注了你！',
            ),
            'from' => array(
                'id' => 1,
                'type' => 'user',
            ),
            'to' => array(
                'type' => 'user',
                'id' => 2,
                'convNo' => 'test convNo',
            ),
        );

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

        $job = new PushJob($context);
        $job->setBiz($this->biz);
        $result = $job->execute();
        $this->assertNull($result);
    }

    /**
     * @return PushService
     */
    protected function getPushService()
    {
        return $this->biz->service('CloudPlatform:PushService');
    }
}
