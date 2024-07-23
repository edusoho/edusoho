<?php

namespace Tests\Unit\AppBundle\Component\OAuthClient;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\OAuthClient\WeiboOAuthClient;
use Biz\BaseTestCase;

class WeiboOAuthClientTest extends BaseTestCase
{
    public function testGetAuthorizeUrl()
    {
        $client = new WeiboOAuthClient(['key' => 'auth_key']);
        $result = $client->getAuthorizeUrl('www.edusoho.com', 'credential');
        $this->assertEquals(
            'https://api.weibo.com/oauth2/authorize?client_id=auth_key&response_type=code&redirect_uri=www.edusoho.com',
            $result
        );
    }

    public function testGetAccessTokenWithReturnError()
    {
        $client = new WeiboOAuthClient(['key' => 'auth_key', 'secret' => 'auth_secret']);

        $request = $this->mockBiz(
            'request',
            [
                [
                    'functionName' => 'postRequest',
                    'withParams' => [
                        'https://api.weibo.com/oauth2/access_token?client_id=auth_key&client_secret=auth_secret&authorization_code=code&redirect_uri=http%3A%2F%2Fwww.edusoho.com&code=code',
                        [
                        ],
                    ],
                    'returnValue' => json_encode([
                        'error' => 'get_id',
                    ]),
                    'times' => 1,
                ],
            ]
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getAccessToken('code', 'http://www.edusoho.com');

        $request->shouldHaveReceived('postRequest')->times(1);

        $this->assertArrayEquals(
            [
                'token' => null,
                'userId' => null,
                'expiredTime' => null,
            ],
            $result
        );
    }

    public function testGetAccessTokenWithoutError()
    {
        $client = new WeiboOAuthClient(['key' => 'auth_key', 'secret' => 'auth_secret']);

        $request = $this->mockBiz(
            'request',
            [
                [
                    'functionName' => 'postRequest',
                    'withParams' => [
                        'https://api.weibo.com/oauth2/access_token?client_id=auth_key&client_secret=auth_secret&authorization_code=code&redirect_uri=http%3A%2F%2Fwww.edusoho.com&code=code',
                        [
                        ],
                    ],
                    'returnValue' => json_encode([
                        'access_token' => 'access_token_id',
                        'uid' => 'uid_id',
                        'expires_in' => 'expires_in_id',
                    ]),
                    'times' => 1,
                ],
            ]
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getAccessToken('code', 'http://www.edusoho.com');
        $request->shouldHaveReceived('postRequest')->times(1);

        $this->assertArrayEquals(
            [
                'token' => 'access_token_id',
                'userId' => 'uid_id',
                'expiredTime' => 'expires_in_id',
            ],
            $result
        );
    }

    public function testGetUserInfo()
    {
        $client = new WeiboOAuthClient(['key' => 'auth_key', 'secret' => 'auth_secret']);
        $request = $this->mockBiz(
            'request',
            [
                [
                    'functionName' => 'getRequest',
                    'withParams' => [
                        'https://api.weibo.com/2/users/show.json',
                        [
                            'access_token' => 'token_token',
                            'uid' => 'userId_id',
                        ],
                    ],
                    'returnValue' => json_encode([
                        'idstr' => 'idstr_id',
                        'screen_name' => 'screen_name_name',
                        'location' => 'location_location',
                        'avatar_hd' => 'avatar_hd_hd',
                    ]),
                    'times' => 1,
                ],
            ]
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getUserInfo(['userId' => 'userId_id', 'token' => 'token_token']);
        $request->shouldHaveReceived('getRequest')->times(1);

        $this->assertArrayEquals(
            [
                'id' => 'idstr_id',
                'name' => 'screen_name_name',
                'location' => 'location_location',
                'avatar' => 'avatar_hd_hd',
            ],
            $result
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testCheckErrorWithCode21321()
    {
        $client = new WeiboOAuthClient(['key' => 'auth_key', 'secret' => 'auth_secret']);
        ReflectionUtils::invokeMethod($client, 'checkError', [['error_code' => '21321']]);
    }

    /**
     * @expectedException \Exception
     */
    public function testCheckErrorWithCode10006()
    {
        $client = new WeiboOAuthClient(['key' => 'auth_key', 'secret' => 'auth_secret']);
        ReflectionUtils::invokeMethod($client, 'checkError', [['error_code' => '10006']]);
    }

    /**
     * @expectedException \Exception
     */
    public function testCheckError()
    {
        $client = new WeiboOAuthClient(['key' => 'auth_key', 'secret' => 'auth_secret']);
        ReflectionUtils::invokeMethod($client, 'checkError', [['error_code' => '10007', 'error' => '1212']]);
    }
}
