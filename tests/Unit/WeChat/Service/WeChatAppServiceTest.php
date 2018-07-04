<?php

namespace Tests\Unit\Thread\Service;

use Biz\BaseTestCase;

class WeChatAppServiceTest extends BaseTestCase
{
    public function testGetWeChatAppStatus()
    {
        $mockedAppService = $this->mockBiz(
            'CloudPlatform:AppService',
            array(
                array(
                    'functionName' => 'getCenterApps',
                    'withParams' => array(),
                    'returnValue' => array(
                        array(
                            'code' => 'wechatapp',
                            'purchased' => true,
                            'latestPackageId' => 11,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'findApps',
                    'withParams' => array(0, 100),
                    'returnValue' => array(
                        array(
                            'code' => 'wechatapp',
                        ),
                    ),
                ),
            )
        );

        $result = $this->getWeChatAppService()->getWeChatAppStatus();
        $this->assertArrayEquals(
            array('purchased' => true, 'installed' => true, 'latestPackageId' => 11),
            $result
        );

        $mockedAppService->shouldHaveReceived('getCenterApps');
        $mockedAppService->shouldHaveReceived('findApps');
    }

    protected function getWeChatAppService()
    {
        return $this->createService('WeChat:WeChatAppService');
    }
}
