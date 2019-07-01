<?php

namespace Tests\Unit\AppBundle\Component\Notification\WeChatTemplateMessage;

use AppBundle\Component\Notification\WeChatTemplateMessage\CloudNotificationClient;
use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class CloudNotificationClientTest extends BaseTestCase
{
    public function testOpenWechatNotification()
    {
        $client = new CloudNotificationClient(array('accessKey' => 'accessKey', 'secretKey' => 'secretKey','app_id'=>'app_id','secret'=>'secret'));

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'request',
                    'withParams' => array(
                        'POST',
                        '/accounts',
                        array('app_id' => 'app_id', 'app_secret' => 'secret', 'type' => 'wechat'),
                    ),
                    'returnValue' => '{"id":3,"user_id":39,"access_key":"T7YARlmDmknXijWTCaRfdBo3O82K63RD","status":1,"created_time":"2019-06-06T09:55:28+00:00","updated_time":"2019-06-09T07:44:12+00:00"}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'testRequest', $request);
        $result = $client->openWechatNotification();
        $this->assertEquals('T7YARlmDmknXijWTCaRfdBo3O82K63RD',$result['access_key']);

    }

    public function testCloseWechatNotification()
    {
        $client = new CloudNotificationClient(array('accessKey' => 'accessKey', 'secretKey' => 'secretKey','app_id'=>'app_id','secret'=>'secret'));

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'request',
                    'withParams' => array(
                        'DELETE',
                        '/accounts/wechat',
                        array(),
                    ),
                    'returnValue' => '{"user_id":39,"type":"wechat","status":0,"created_time":"2019-06-06T09:55:28+00:00","updated_time":"2019-06-09T08:01:23+00:00"}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'testRequest', $request);
        $result = $client->closeWechatNotification();
        $this->assertEquals('wechat',$result['type']);

    }

    public function testSendWechatNotificaion()
    {
        $client = new CloudNotificationClient(array('accessKey' => 'accessKey', 'secretKey' => 'secretKey','app_id'=>'app_id','secret'=>'secret'));

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'request',
                    'withParams' => array(
                        'POST',
                        '/notifications',
                        array('[{"channel" : "wechat", "to_id": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M","title": "xxx","content": "xxxxxx","template_id": "xxxxx"]'),
                    ),
                    'returnValue' => '{"success":true}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'testRequest', $request);
        $result = $client->sendWechatNotificaion(array('[{"channel" : "wechat", "to_id": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M","title": "xxx","content": "xxxxxx","template_id": "xxxxx"]'));
        $this->assertTrue($result['success']);

    }

    public function testGetNotificationSendResult()
    {
        $client = new CloudNotificationClient(array('accessKey' => 'accessKey', 'secretKey' => 'secretKey','app_id'=>'app_id','secret'=>'secret'));

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'request',
                    'withParams' => array(
                        'GET',
                        '/notifications/d54676fa85f211e9a177186590d302a3',
                        array()
                    ),
                    'returnValue' => '{"sn": "d54676fa85f211e9a177186590d302a3","total_count": 1,"succeed_count": 0,"failure_reason": null}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'testRequest', $request);
        $result = $client->getNotificationSendResult('d54676fa85f211e9a177186590d302a3');
        $this->assertEquals(1,$result['total_count']);

    }

    public function testBatchGetNotificationsSendResult()
    {
        $client = new CloudNotificationClient(array('accessKey' => 'accessKey', 'secretKey' => 'secretKey','app_id'=>'app_id','secret'=>'secret'));
        $result1 = $client->batchGetNotificationsSendResult();

        $request = $this->mockBiz(
            'request',
            array(
                array(
                    'functionName' => 'request',
                    'withParams' => array(
                        'GET',
                        '/notifications',
                        array()
                    ),
                    'returnValue' => '{"success":true}',
                    'times' => 1,
                ),
            )
        );

        ReflectionUtils::setProperty($client, 'testRequest', $request);
        $result = $client->batchGetNotificationsSendResult();
        $this->assertEmpty($result1);
        $this->assertTrue($result['success']);
    }
}
