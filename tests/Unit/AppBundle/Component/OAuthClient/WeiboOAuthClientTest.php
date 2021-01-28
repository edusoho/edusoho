<?php

namespace Tests\Unit\AppBundle\Component\OAuthClient;

use Biz\BaseTestCase;
use AppBundle\Component\OAuthClient\WeiboOAuthClient;
use AppBundle\Common\ReflectionUtils;

class WeiboOAuthClientTest extends BaseTestCase
{
    public function testGetAuthorizeUrl()
    {
        $client = new WeiboOAuthClient(array('key' => 'auth_key'));
        $result = $client->getAuthorizeUrl('www.edusoho.com');
        $this->assertEquals(
            'https://api.weibo.com/oauth2/authorize?client_id=auth_key&response_type=code&redirect_uri=www.edusoho.com',
            $result
        );
    }

    public function testGetAccessTokenWithReturnError()
    {
        $client = new WeiboOAuthClient(array('key' => 'auth_key', 'secret' => 'auth_secret'));

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'postRequest',
                    'withParams' => array(
                        'https://api.weibo.com/oauth2/access_token?client_id=auth_key&client_secret=auth_secret&authorization_code=code&redirect_uri=http%3A%2F%2Fwww.edusoho.com&code=code',
                        array(
                        ),
                    ),
                    'returnValue' => json_encode(array(
                        'error' => 'get_id',
                    )),
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getAccessToken('code', 'http://www.edusoho.com');

        $request->shouldHaveReceived('postRequest')->times(1);

        $this->assertArrayEquals(
            array(
                'token' => null,
                'userId' => null,
                'expiredTime' => null,
            ),
            $result
        );
    }

    public function testGetAccessTokenWithoutError()
    {
        $client = new WeiboOAuthClient(array('key' => 'auth_key', 'secret' => 'auth_secret'));

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'postRequest',
                    'withParams' => array(
                        'https://api.weibo.com/oauth2/access_token?client_id=auth_key&client_secret=auth_secret&authorization_code=code&redirect_uri=http%3A%2F%2Fwww.edusoho.com&code=code',
                        array(
                        ),
                    ),
                    'returnValue' => json_encode(array(
                        'access_token' => 'access_token_id',
                        'uid' => 'uid_id',
                        'expires_in' => 'expires_in_id',
                    )),
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getAccessToken('code', 'http://www.edusoho.com');
        $request->shouldHaveReceived('postRequest')->times(1);

        $this->assertArrayEquals(
            array(
                'token' => 'access_token_id',
                'userId' => 'uid_id',
                'expiredTime' => 'expires_in_id',
            ),
            $result
        );
    }

    public function testGetUserInfo()
    {
        $client = new WeiboOAuthClient(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'getRequest',
                    'withParams' => array(
                        'https://api.weibo.com/2/users/show.json',
                        array(
                            'access_token' => 'token_token',
                            'uid' => 'userId_id',
                        ),
                    ),
                    'returnValue' => json_encode(array(
                        'idstr' => 'idstr_id',
                        'screen_name' => 'screen_name_name',
                        'location' => 'location_location',
                        'avatar_hd' => 'avatar_hd_hd',
                    )),
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getUserInfo(array('userId' => 'userId_id', 'token' => 'token_token'));
        $request->shouldHaveReceived('getRequest')->times(1);

        $this->assertArrayEquals(
            array(
                'id' => 'idstr_id',
                'name' => 'screen_name_name',
                'location' => 'location_location',
                'avatar' => 'avatar_hd_hd',
            ),
            $result
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testCheckErrorWithCode21321()
    {
        $client = new WeiboOAuthClient(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        ReflectionUtils::invokeMethod($client, 'checkError', array(array('error_code' => '21321')));
    }

    /**
     * @expectedException \Exception
     */
    public function testCheckErrorWithCode10006()
    {
        $client = new WeiboOAuthClient(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        ReflectionUtils::invokeMethod($client, 'checkError', array(array('error_code' => '10006')));
    }

    /**
     * @expectedException \Exception
     */
    public function testCheckError()
    {
        $client = new WeiboOAuthClient(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        ReflectionUtils::invokeMethod($client, 'checkError', array(array('error_code' => '10007', 'error' => '1212')));
    }
}
