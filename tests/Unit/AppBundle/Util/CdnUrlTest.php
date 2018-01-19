<?php

namespace Tests\Unit\AppBundle\Component\RateLimit;

use Biz\BaseTestCase;
use AppBundle\Util\CdnUrl;
use AppBundle\Common\ReflectionUtils;

class CdnUrlTest extends BaseTestCase
{
    public function testGet()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('cdn', array()),
                    'returnValue' => array(
                        'enabled' => 1,
                        'defaultUrl' => '\\kuozhi.com',
                        'userUrl' => '\\kuozhi.user',
                        'contentUrl' => '\\kuozhi.content',
                    ),
                ),
            )
        );

        $cdnUrl = new CdnUrl();
        $result = $cdnUrl->get('user');
        $settingService->shouldHaveReceived('get')->times(1);
        $this->assertEquals('\kuozhi.user', $result);
    }

    public function testUrlWithHttps()
    {
        $cdnUrl = new CdnUrl();
        $result = ReflectionUtils::invokeMethod($cdnUrl, 'url', array('https://kuozhi.com'));
        $this->assertEquals('kuozhi.com', $result);
    }

    public function testUrlWithHttp()
    {
        $cdnUrl = new CdnUrl();
        $result = ReflectionUtils::invokeMethod($cdnUrl, 'url', array('http://kuozhi.com'));
        $this->assertEquals('kuozhi.com', $result);
    }
}
