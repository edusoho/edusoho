<?php

namespace Tests\Unit\AppBundle\Component\OAuthClient;

use Biz\BaseTestCase;
use AppBundle\Component\OAuthClient\WeixinwebOAuthClient;
use AppBundle\Common\ReflectionUtils;

class WeixinWebOAuthClientTest extends BaseTestCase
{
    public function testGetAuthorizeUrl()
    {
        $client = new WeixinwebOAuthClient(array('key' => 'auth_key'));
        $result = $client->getAuthorizeUrl('www.edusoho.com');
        $this->assertEquals(
            'https://open.weixin.qq.com/connect/qrconnect?appid=auth_key&response_type=code&redirect_uri=www.edusoho.com&scope=snsapi_login',
            $result
        );
    }

    public function testGetAccessToken()
    {
        $client = new WeixinwebOAuthClient(array('key' => 'auth_key', 'secret' => 'auth_secret'));

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'getRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/sns/oauth2/access_token',
                        array(
                            'appid' => 'auth_key',
                            'secret' => 'auth_secret',
                            'code' => 'code',
                            'grant_type' => 'authorization_code',
                        ),
                    ),
                    'returnValue' => json_encode(array(
                        'expires_in' => 1231,
                        'access_token' => 'get_access_token',
                        'openid' => 'get_openid',
                    )),
                    'times' => 1,
                ),
                array(
                    'functionName' => 'getRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/sns/userinfo',
                        array(
                            'openid' => 'get_openid',
                            'access_token' => 'get_access_token',
                        ),
                    ),
                    'returnValue' => json_encode(array(
                        'openid' => 'get_openid',
                        'unionid' => 'get_unionid',
                        'nickname' => 'get_nickname',
                        'headimgurl' => 'get_headimgurl.png',
                        'sex' => 1,
                    )),
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getAccessToken('code', 'http://www.edusoho.com');

        $request->shouldHaveReceived('getRequest')->times(2);

        $this->assertArrayEquals(
            array(
                'userId' => 'get_unionid',
                'expiredTime' => '1231',
                'access_token' => 'get_access_token',
                'token' => 'get_access_token',
                'openid' => 'get_openid',
            ),
            $result
        );
    }
}
