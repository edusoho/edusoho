<?php

namespace Tests\Unit\AppBundle\Component\OAuthClient;

use Biz\BaseTestCase;
use AppBundle\Component\OAuthClient\WeixinwebOAuthClient;

class AbstractOAuthClientTest extends BaseTestCase
{
    public function testMakeToken()
    {
        $client = new WeixinwebOAuthClient(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $this->assertArrayEquals(
            array(
                'userId' => 'test_openId',
                'token' => 'test_accessToken',
            ),
            $client->makeToken('weibo', 'test_accessToken', 'test_openId', 'test_appId')
        );

        $this->assertArrayEquals(
            array(
                'openid' => 'test_openId',
                'access_token' => 'test_accessToken',
                'key' => 'test_appId',
            ),
            $client->makeToken('qq', 'test_accessToken', 'test_openId', 'test_appId')
        );

        $this->assertArrayEquals(
            array(
                'openid' => 'test_openId',
                'access_token' => 'test_accessToken',
            ),
            $client->makeToken('weixinmob', 'test_accessToken', 'test_openId', 'test_appId')
        );

        $this->assertArrayEquals(
            array(
                'openid' => 'test_openId',
                'access_token' => 'test_accessToken',
            ),
            $client->makeToken('weixinweb', 'test_accessToken', 'test_openId', 'test_appId')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMakeTokenWithException()
    {
        $client = new WeixinwebOAuthClient(array('key' => 'auth_key', 'secret' => 'auth_secret'));

        // 这里会抛出异常
        $client->makeToken('des', 'test_accessToken', 'test_openId', 'test_appId');
    }

    public function testPostRequest()
    {
        $client = new WeixinwebOAuthClient(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $result = $client->postRequest('http://www.edusoho.com', array());
        $this->assertTrue(strpos($result, '阔知') != -1);
    }

    public function testGetRequest()
    {
        $client = new WeixinwebOAuthClient(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $result = $client->getRequest('http://www.edusoho.com', array());
        $this->assertTrue(strpos($result, '阔知') != -1);
    }
}
