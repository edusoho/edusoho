<?php

namespace Tests\Unit\AppBundle\Component\OAuthClient;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\OAuthClient\AppleOAuthClient;
use Biz\BaseTestCase;

class AppleAuthClientTest extends BaseTestCase
{
    public function testGetAuthorizeUrl()
    {
        $config = [
            'clientId' => 'test',
            'teamId' => 'AJSHDDSDSD',
            'keyId' => '88727322JXY',
            'secretKey' => 'secret',
        ];
        $client = new AppleOAuthClient($config);
        $result = $client->getAuthorizeUrl('www.edusoho.cn');
        $this->assertEquals(
            'https://appleid.apple.com/auth/authorize?client_id=test&redirect_uri=www.edusoho.cn&response_type=code&scope=scope&state=es-state',
            $result
        );
    }

    public function testGetAccessToken()
    {
        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'postRequest',
                    'returnValue' => json_encode(array(
                        'access_token' => 'access_token',
                        'token_type' => 'Bearer',
                        'expires_in' => 3600,
                        'refresh_token' => 'refresh_token',
                        'id_token' => 'id_token'
                    )),
                    'times' => 1,
                ),
            )
        );

        $config = [
            'clientId' => 'test',
            'teamId' => 'AJSHDDSDSD',
            'key' => '88727322JXY',
            'secret' => '-----BEGIN EC PRIVATE KEY-----
MHcCAQEEILogrrEub22bnn1Ztos8DvPBX4o77KZ0dasyHFctzPGSoAoGCCqGSM49
AwEHoUQDQgAEnWWUGlN+fRXtDHstWCv1Y0p9tNrse3LX67+vbPiUe2voec9hLXMw
t6i4YAveYBmNFft5YnyMnWztZzcxt878oA==
-----END EC PRIVATE KEY-----',
        ];
        $client = new AppleOAuthClient($config);
        ReflectionUtils::setProperty($client, 'request', $request);

        $result = $client->getAccessToken('testCode', '');

        $this->assertArrayEquals(
            array(
                'access_token' => 'access_token',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'refresh_token' => 'refresh_token',
                'id_token' => 'id_token'
            ),
            $result
        );
    }

    public function testGetUserInfo()
    {
        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'postRequest',
                    'returnValue' => json_encode(array(
                        'access_token' => 'access_token',
                        'token_type' => 'Bearer',
                        'expires_in' => 3600,
                        'refresh_token' => 'refresh_token',
                        'id_token' => 'test.'.base64_encode(json_encode(['sub' => 'openid'])).'.test',
                    )),
                    'times' => 1,
                ),
            )
        );

        $config = [
            'clientId' => 'test',
            'teamId' => 'AJSHDDSDSD',
            'key' => '88727322JXY',
            'secret' => '-----BEGIN EC PRIVATE KEY-----
MHcCAQEEILogrrEub22bnn1Ztos8DvPBX4o77KZ0dasyHFctzPGSoAoGCCqGSM49
AwEHoUQDQgAEnWWUGlN+fRXtDHstWCv1Y0p9tNrse3LX67+vbPiUe2voec9hLXMw
t6i4YAveYBmNFft5YnyMnWztZzcxt878oA==
-----END EC PRIVATE KEY-----',
        ];
        $client = new AppleOAuthClient($config);
        ReflectionUtils::setProperty($client, 'request', $request);

        $result = $client->getUserInfo(['access_token' => 'testCode', 'openid' => 'openid']);
        $this->assertEquals($result['id'], 'openid');
    }
}
