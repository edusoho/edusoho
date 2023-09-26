<?php

namespace Tests\Unit\AppBundle\Component\OAuthClient;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\OAuthClient\WeixinmobOAuthClient;
use Biz\BaseTestCase;

class WeixinmobOAuthClientTest extends BaseTestCase
{
    public function testGetAuthorizeUrl()
    {
        $client = new WeixinmobOAuthClient(['key' => 'auth_key']);
        $result = $client->getAuthorizeUrl('www.edusoho.com', 'credential');
        $this->assertEquals(
            'https://open.weixin.qq.com/connect/oauth2/authorize?appid=auth_key&redirect_uri=www.edusoho.com&response_type=code&scope=snsapi_userinfo#wechat_redirect',
            $result
        );
    }

    public function testGetAccessToken()
    {
        $client = new WeixinmobOAuthClient(['key' => 'auth_key', 'secret' => 'auth_secret']);

        $request = $this->mockBiz(
            'request',
            [
                [
                    'functionName' => 'getRequest',
                    'withParams' => [
                        'https://api.weixin.qq.com/sns/oauth2/access_token',
                        [
                            'appid' => 'auth_key',
                            'secret' => 'auth_secret',
                            'code' => 'code',
                            'grant_type' => 'authorization_code',
                        ],
                    ],
                    'returnValue' => json_encode([
                        'expires_in' => 1231,
                        'access_token' => 'get_access_token',
                        'openid' => 'get_openid',
                    ]),
                    'times' => 1,
                ],
                [
                    'functionName' => 'getRequest',
                    'withParams' => [
                        'https://api.weixin.qq.com/sns/userinfo',
                        [
                            'openid' => 'get_openid',
                            'access_token' => 'get_access_token',
                            'lang' => 'zh_CN',
                        ],
                    ],
                    'returnValue' => json_encode([
                        'openid' => 'get_openid',
                        'unionid' => 'get_unionid',
                        'nickname' => 'get_nickname',
                        'headimgurl' => 'get_headimgurl.png',
                        'sex' => 1,
                    ]),
                    'times' => 1,
                ],
            ]
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getAccessToken('code', 'http://www.edusoho.com');

        $request->shouldHaveReceived('getRequest')->times(2);

        $this->assertArrayEquals(
            [
                'userId' => 'get_unionid',
                'expiredTime' => '1231',
                'access_token' => 'get_access_token',
                'token' => 'get_access_token',
                'openid' => 'get_openid',
            ],
            $result
        );
    }
}
