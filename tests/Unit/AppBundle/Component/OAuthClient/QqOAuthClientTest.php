<?php

namespace Tests\Unit\AppBundle\Component\OAuthClient;

use Biz\BaseTestCase;
use AppBundle\Component\OAuthClient\QqOAuthClient;
use AppBundle\Common\ReflectionUtils;

class QqOAuthClientTest extends BaseTestCase
{
    public function testGetAuthorizeUrl()
    {
        $client = new QqOAuthClient(array('key' => 'auth_key'));
        $result = $client->getAuthorizeUrl('www.edusoho.com');
        $this->assertEquals(
            'https://graph.qq.com/oauth2.0/authorize?client_id=auth_key&response_type=code&redirect_uri=www.edusoho.com&status=pro',
            $result
        );
    }

    public function testGetAccessToken()
    {
        $client = new QqOAuthClient(array('key' => 'auth_key', 'secret' => 'auth_secret'));

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'getRequest',
                    'withParams' => array(
                        'https://graph.qq.com/oauth2.0/token',
                        array(
                            'grant_type' => 'authorization_code',
                            'client_id' => 'auth_key',
                            'redirect_uri' => 'http://www.edusoho.com',
                            'client_secret' => 'auth_secret',
                            'code' => 'code',
                        ),
                    ),
                    'returnValue' => 'expires_in=1231&access_token=get_access_token',
                    'times' => 1,
                ),
                array(
                    'functionName' => 'getRequest',
                    'withParams' => array(
                        'https://graph.qq.com/oauth2.0/me',
                        array(
                            'access_token' => 'get_access_token',
                        ),
                    ),
                    'returnValue' => 'callback=('.json_encode(array('openid' => 'get_openid')).')',
                    'times' => 1,
                ),
                array(
                    'functionName' => 'getRequest',
                    'withParams' => array(
                        'https://graph.qq.com/user/get_user_info',
                        array(
                            'oauth_consumer_key' => 'auth_key',
                            'openid' => 'get_openid',
                            'format' => 'json',
                            'access_token' => 'get_access_token',
                        ),
                    ),
                    'returnValue' => json_encode(array(
                        'id' => 'get_id',
                        'nickname' => 'get_nickname',
                        'figureurl_qq_2' => 'get_figureurl_qq_2',
                        'gender' => 'get_gender',
                    )),
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getAccessToken('code', 'http://www.edusoho.com');

        $request->shouldHaveReceived('getRequest')->times(3);

        $this->assertArrayEquals(
            array(
                'userId' => 'get_openid',
                'expiredTime' => '1231',
                'access_token' => 'get_access_token',
                'token' => 'get_access_token',
            ),
            $result
        );
    }
}
