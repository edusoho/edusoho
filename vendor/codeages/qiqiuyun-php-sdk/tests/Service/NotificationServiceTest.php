<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Constants\NotificationChannelTypes;
use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\NotificationService;

class NotificationServiceTest extends BaseTestCase
{
    public function testOpenAccount()
    {
        $httpClient = $this->mockHttpClient(array(
            'id' => 3,
            'status' => 1,
            'created_time' => '2019-06-06T09:55:28+00:00',
            'updated_time' => '2019-06-09T07:44:12+00:00',
        ));

        $service = new NotificationService($this->auth, array(), null, $httpClient);

        $result = $service->openAccount();

        $this->assertEquals(1, $result['status']);
    }

    public function testCloseAccount()
    {
        $httpClient = $this->mockHttpClient(array(
            'id' => 3,
            'status' => 0,
            'created_time' => '2019-06-06T09:55:28+00:00',
            'updated_time' => '2019-06-09T07:44:12+00:00',
        ));

        $service = new NotificationService($this->auth, array(), null, $httpClient);

        $result = $service->openAccount();

        $this->assertEquals(0, $result['status']);
    }

    public function testOpenChannel()
    {
        $httpClient = $this->mockHttpClient(array(
            'user_id' => 39,
            'type' => NotificationChannelTypes::WECHAT,
            'status' => 1,
            'created_time' => '2019-06-06T09:55:28+00:00',
            'updated_time' => '2019-06-09T08:01:23+00:00',
        ));

        $channelType = NotificationChannelTypes::WECHAT;
        $params = array(
            'app_id' => '12345',
            'app_secret' => '123456',
        );
        $service = new NotificationService($this->auth, array(), null, $httpClient);

        $result = $service->openChannel($channelType, $params);
        $this->assertEquals(array(
            'user_id' => 39,
            'type' => NotificationChannelTypes::WECHAT,
            'status' => 1,
            'created_time' => '2019-06-06T09:55:28+00:00',
            'updated_time' => '2019-06-09T08:01:23+00:00',
        ), $result);
    }

    public function testCloseChannel()
    {
        $httpClient = $this->mockHttpClient(array(
            'user_id' => 39,
            'type' => NotificationChannelTypes::WECHAT,
            'status' => 0,
            'created_time' => '2019-06-06T09:55:28+00:00',
            'updated_time' => '2019-06-09T08:01:23+00:00',
        ));

        $channelType = NotificationChannelTypes::WECHAT;
        $params = array(
            'app_id' => '12345',
            'app_secret' => '123456',
        );
        $service = new NotificationService($this->auth, array(), null, $httpClient);

        $result = $service->openChannel($channelType, $params);
        $this->assertEquals(array(
            'user_id' => 39,
            'type' => NotificationChannelTypes::WECHAT,
            'status' => 0,
            'created_time' => '2019-06-06T09:55:28+00:00',
            'updated_time' => '2019-06-09T08:01:23+00:00',
        ), $result);
    }

    public function testSendNotifications()
    {
        $httpClient = $this->mockHttpClient(array(
            'sn' => '123456789',
        ));
        $params = array(
            array(
                'channel' => NotificationChannelTypes::WECHAT,
                'to_id' => 'o6_bmjrPTlm6_2sgVt7hMZOPfL2M',
                'title' => 'xxx',
                'content' => 'xxxxxx',
                'template_id' => 'xxxxx',
                'template_args' => array(
                    'keyword1' => array(
                        'value' => '2014年9月22日',
                        'color' => '#173177',
                    ),
                    'remark' => array(
                        'value' => '欢迎再次购买！',
                        'color' => '#173177',
                    ),
                ),
                'goto' => array(
                    'type' => 'url',
                    'url' => 'www.baidu.com',
                ),
            ),
        );
        $service = new NotificationService($this->auth, array(), null, $httpClient);

        $result = $service->searchNotifications($params);
        $this->assertEquals('123456789', $result['sn']);
    }

    public function testBatchGetNotifications()
    {
        $httpClient = $this->mockHttpClient(array(
            'data' => array(
                'sn' => '12345678',
                'total_count' => 1,
                'succeed_count' => 1,
                'failure_reason' => array(),
                'is_finished' => 1,
                'finished_time' => '2019-06-09T08:01:23+00:00',
                'created_time' => '2019-06-09T08:01:23+00:00',
                'updated_time' => '2019-06-09T08:01:23+00:00',
            ),
            'paging' => array(
                'total' => 100,
                'offset' => 0,
                'limit' => 1,
            ),
        ));

        $service = new NotificationService($this->auth, array(), null, $httpClient);

        $result = $service->batchGetNotifications(array('12345678'));
        $this->assertEquals(array(
            'data' => array(
                'sn' => '12345678',
                'total_count' => 1,
                'succeed_count' => 1,
                'failure_reason' => array(),
                'is_finished' => 1,
                'finished_time' => '2019-06-09T08:01:23+00:00',
                'created_time' => '2019-06-09T08:01:23+00:00',
                'updated_time' => '2019-06-09T08:01:23+00:00',
            ),
            'paging' => array(
                'total' => 100,
                'offset' => 0,
                'limit' => 1,
            ),
        ), $result);
    }
}
