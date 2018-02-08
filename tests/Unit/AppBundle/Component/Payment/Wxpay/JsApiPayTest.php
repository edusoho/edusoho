<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\Payment\Wxpay\JsApiPay;

class JsApiPayTest extends BaseTestCase
{
    public function testGetOpenIdPerSession()
    {
        $mockedSession = $this->mockBiz(
            'Session',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('openid'),
                    'returnValue' => '1234421223desf',
                ),
            )
        );
        $mockedRequest = $this->mockBiz(
            'Request',
            array(
                array(
                    'functionName' => 'getSession',
                    'withParams' => array(),
                    'returnValue' => $mockedSession,
                ),
            )
        );

        $pay = new JsApiPay(array(), $mockedRequest);

        $this->assertEquals('1234421223desf', $pay->getOpenid());
        $mockedSession->shouldHaveReceived('get')->times(1);
        $mockedRequest->shouldHaveReceived('getSession')->times(1);
    }

    public function testGetOpenIdPerQuery()
    {
        $mockedSession = $this->mockBiz(
            'Session',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('openid'),
                    'returnValue' => '',
                ),
                array(
                    'functionName' => 'set',
                    'withParams' => array('openid', '1221233'),
                    'returnValue' => '',
                ),
            )
        );
        $mockedRequest = $this->mockBiz(
            'Request',
            array(
                array(
                    'functionName' => 'getSession',
                    'withParams' => array(),
                    'returnValue' => $mockedSession,
                ),
            )
        );
        $mockedQuery = $this->mockBiz(
            'Request',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('code'),
                    'returnValue' => 'query-code',
                ),
            )
        );

        $mockedRequest->query = $mockedQuery;

        $pay = new JsApiPay(array('appid' => 'appid', 'secret' => 'secret'), $mockedRequest);

        $pay = ReflectionUtils::setProperty($pay, 'mockedCurl', array('openid' => 1221233));

        $this->assertEquals('1221233', $pay->getOpenid());

        $mockedSession->shouldHaveReceived('get')->times(1);
        $mockedSession->shouldHaveReceived('set')->times(1);
        $mockedRequest->shouldHaveReceived('getSession')->times(2);
        $mockedQuery->shouldHaveReceived('get')->times(2);
    }

    public function test__createOauthUrlForCode()
    {
        $pay = new JsApiPay(array('appid' => 'appid', 'secret' => 'secret', 'redirect_uri' => 'redirect_uri'), null);

        $result = ReflectionUtils::invokeMethod($pay, '__createOauthUrlForCode');

        $this->assertEquals('https://open.weixin.qq.com/connect/oauth2/authorize?appid=appid&redirect_uri=redirect_uri&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect', $result);
    }

    public function test__createOauthUrlForOpenid()
    {
        $pay = new JsApiPay(array('appid' => 'appid', 'secret' => 'secret', 'redirect_uri' => 'redirect_uri'), null);

        $result = ReflectionUtils::invokeMethod($pay, '__createOauthUrlForOpenid', array('auth_code'));

        $this->assertEquals('https://api.weixin.qq.com/sns/oauth2/access_token?appid=appid&secret=secret&code=auth_code&grant_type=authorization_code', $result);
    }
}
