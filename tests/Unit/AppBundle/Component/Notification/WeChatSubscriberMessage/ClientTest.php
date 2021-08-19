<?php

namespace Tests\Unit\AppBundle\Component\Notification\WeChatSubscriberMessage;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\Notification\WeChatSubscriberMessage\Client;
use Biz\BaseTestCase;

class ClientTest extends BaseTestCase
{
    public function testGetAccessToken()
    {
        $client = new Client(['key' => 'auth_key', 'secret' => 'auth_secret']);
        $result1 = $client->getAccessToken();

        $request = $this->mockBiz(
            'request',
            [
                [
                    'functionName' => 'getRequest',
                    'withParams' => [
                        'https://api.weixin.qq.com/cgi-bin/token',
                        [
                            'appid' => 'auth_key',
                            'secret' => 'auth_secret',
                            'grant_type' => 'client_credential',
                        ],
                    ],
                    'returnValue' => '{"access_token":"ACCESS_TOKEN","expires_in":7200}',
                    'times' => 1,
                ],
            ]
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getAccessToken();
        $this->assertEmpty($result1);
        $this->assertEquals('ACCESS_TOKEN', $result['access_token']);
    }

    public function testSendMessage()
    {
        $client = new Client(['key' => 'auth_key', 'secret' => 'auth_secret']);
        $result1 = $client->sendMessage('openId', 'templateId', []);
        $request = $this->mockBiz(
            'request',
            [
                [
                    'functionName' => 'postRequest',
                    'withParams' => [
                        'https://api.weixin.qq.com/cgi-bin/message/subscribe/bizsend',
                        [
                            'touser' => 'openId',
                            'template_id' => 'templateId',
                            'data' => [],
                        ],
                    ],
                    'returnValue' => '{"success":"true"}',
                    'times' => 1,
                ],
            ]
        );
        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->sendMessage('openId', 'templateId', []);
        $this->assertEmpty($result1);
        $this->assertEquals('true', $result['success']);
    }
}
