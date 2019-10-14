<?php

namespace Tests\Unit\AppBundle\Component\ShareSdk;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\ShareSdk\WeixinShare;
use Biz\BaseTestCase;

class WeixinShareTest extends BaseTestCase
{
    public function testSetLogger()
    {
        $config = array();

        $result = ReflectionUtils::getProperty(new WeixinShare($config), 'logger');
        $this->assertInstanceOf('Monolog\Logger', $result);
    }

    public function testGetAccessToken()
    {
        $config = array(
            'key' => 'testKey',
            'secret' => 'testSecret',
        );

        $request = array(
            'errmsg' => 'error',
            'expires_in' => time() + 60 * 2,
            'access_token' => 'testToken',
        );

        $weixinShare = new WeixinShare($config);
        $weixinShare->setRequest($request);
        $result = $weixinShare->getAccessToken();
        $this->assertEmpty($result);

        $request = array(
            'errmsg' => 'ok',
            'expires_in' => time() + 60 * 2,
            'access_token' => 'testToken',
        );

        $weixinShare = new WeixinShare($config);
        $weixinShare->setRequest($request);
        $result = $weixinShare->getAccessToken();

        unset($request['errmsg']);
        $this->assertEquals($request, $result);
    }

    public function testGetJjsApiTicket()
    {
        $config = array(
            'key' => 'testKey',
            'secret' => 'testSecret',
        );
        $weixinShare = new WeixinShare($config);

        $result = $weixinShare->getJsApiTicket();
        $this->assertEmpty($result);

        $request = array(
            'errmsg' => 'ok',
            'expires_in' => time() + 3600 * 2,
            'access_token' => 'testToken',
            'ticket' => 'testTicket',
        );

        $weixinShare = new WeixinShare($config);
        $weixinShare->setRequest($request);

        $result = $weixinShare->getJsApiTicket();

        unset($request['errmsg']);
        unset($request['access_token']);
        $this->assertEquals($request, $result);
    }
}
