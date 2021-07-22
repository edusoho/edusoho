<?php

namespace Tests\Unit\AppBundle\Component\Notification\WeChatSubscriberMessage;

use AppBundle\Component\Notification\WeChatSubscriberMessage\Client;
use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class ClientTest extends BaseTestCase
{
    public function testGetAccessToken()
    {
        $client = new Client(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $result1 = $client->getAccessToken();

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'getRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/cgi-bin/token',
                        array(
                            'appid' => 'auth_key',
                            'secret' => 'auth_secret',
                            'grant_type' => 'client_credential',
                        ),
                    ),
                    'returnValue' => '{"access_token":"ACCESS_TOKEN","expires_in":7200}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->getAccessToken();
        $this->assertEmpty($result1);
        $this->assertEquals('ACCESS_TOKEN', $result['access_token']);
    }

    public function testSendMessage()
    {
        $client = new Client(array('key' => 'auth_key', 'secret' => 'auth_secret'));
        $result1 = $client->sendMessage('openId', 'templateId', []);
        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'postRequest',
                    'withParams' => array(
                        'https://api.weixin.qq.com/cgi-bin/message/subscribe/bizsend',
                        array(
                            'touser' => 'openId',
                            'template_id' => 'templateId',
                            'data' => [],
                        ),
                    ),
                    'returnValue' => '{"success":"true"}',
                    'times' => 1,
                ),
            )
        );
        ReflectionUtils::setProperty($client, 'request', $request);
        $result = $client->sendMessage('openId', 'templateId', []);
        $this->assertEmpty($result1);
        $this->assertEquals('true', $result['success']);
    }
}