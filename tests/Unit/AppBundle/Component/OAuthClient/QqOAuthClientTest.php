<?php

namespace Tests\Unit\AppBundle\Component\OAuthClient;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\OAuthClient\QqOAuthClient;
use Biz\BaseTestCase;

class QqOAuthClientTest extends BaseTestCase
{
    public function testGetAuthorizeUrl()
    {
        $client = new QqOAuthClient(['key' => 'auth_key']);
        $result = $client->getAuthorizeUrl('www.edusoho.com', 'credential');
        $this->assertEquals(
            'https://graph.qq.com/oauth2.0/authorize?client_id=auth_key&response_type=code&redirect_uri=www.edusoho.com&status=pro',
            $result
        );
    }

    public function testGetAccessToken()
    {
        $client = new QqOAuthClient(['key' => 'auth_key', 'secret' => 'auth_secret']);

        $request = $this->mockBiz(
            'request',
            [
                [
                    'functionName' => 'getRequest',
                    'withParams' => [
                        'https://graph.qq.com/oauth2.0/token',
                        [
                            'grant_type' => 'authorization_code',
                            'client_id' => 'auth_key',
                            'redirect_uri' => 'http://www.edusoho.com',
                            'client_secret' => 'auth_secret',
                            'code' => 'code',
                        ],
                    ],
                    'returnValue' => 'expires_in=1231&access_token=get_access_token',
                    'times' => 1,
                ],
                [
                    'functionName' => 'getRequest',
                    'withParams' => [
                        'https://graph.qq.com/oauth2.0/me',
                        [
                            'access_token' => 'get_access_token',
                        ],
                    ],
                    'returnValue' => 'callback=('.json_encode(['openid' => 'get_openid']).')',
                    'times' => 1,
                ],
                [
                    'functionName' => 'getRequest',
                    'withParams' => [
                        'https://graph.qq.com/user/get_user_info',
                        [
                            'oauth_consumer_key' => 'auth_key',
                            'openid' => 'get_openid',
                            'format' => 'json',
                            'access_token' => 'get_access_token',
                        ],
                    ],
                    'returnValue' => json_encode([
                        'id' => 'get_id',
                        'nickname' => 'get_nickname',
                        'figureurl_qq_2' => 'get_figureurl_qq_2',
                        'gender' => 'get_gender',
                    ]),
                    'times' => 1,
                ],
            ]
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getAccessToken('code', 'http://www.edusoho.com');

        $request->shouldHaveReceived('getRequest')->times(3);

        $this->assertArrayEquals(
            [
                'userId' => 'get_openid',
                'expiredTime' => '1231',
                'access_token' => 'get_access_token',
                'token' => 'get_access_token',
            ],
            $result
        );
    }
}
