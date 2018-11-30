<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\AiService;

class AiServiceTest extends BaseTestCase
{
    public function testCreateFaceSession()
    {
        $mockSession = $this->mockSession();
        $httpClient = $this->mockHttpClient($mockSession);

        $service = new AiService($this->auth, array(), null, $httpClient);

        $userId = $mockSession['user']['id'];
        $userName = $mockSession['user']['username'];
        $type = $mockSession['type'];

        $result = $service->createFaceSession($userId, $userName, $type);

        $this->assertEquals($userId, $result['user']['id']);
        $this->assertEquals($userName, $result['user']['username']);
        $this->assertEquals('created', $result['status']);
    }

    public function testGetFaceSession()
    {
        $mockSession = $this->mockSession();

        $httpClient = $this->mockHttpClient($mockSession);

        $service = new AiService($this->auth, array(), null, $httpClient);

        $result = $service->getFaceSession($mockSession['id']);

        $this->assertEquals('created', $result['status']);
    }

    public function testFinishFaceUpload()
    {
        $mockSession = $this->mockSession();

        $httpClient = $this->mockHttpClient(array(
            'success' => true,
        ));

        $service = new AiService($this->auth, array(), null, $httpClient);

        $result = $service->finishFaceUpload($mockSession['id'], 200, '{"hash":"33df3df3df33","key":"33df3df3df33"}');

        $this->assertEquals(true, $result['success']);
    }

    private function mockSession()
    {
        return array(
            'id' => 'cdfed62d29ec2b5c38c385b7a2cf470a9e4743177397e028bb3af15508a26658',
            'type' => 'register',
            'status' => 'created',
            'user' => array(
                'id' => '333',
                'username' => 'clf',
            ),
            'face' => array(
                'token' => 'd62d29d62d29d62d29',
                'provider' => 'baidu',
            ),
            'upload' => array(
                'provider' => 'qiniu',
                'key' => 'face/libs/vPb16d4L9YFm9mqlvTyoCo0Y5og1vZL/5cbab0e19c3b4effbf29e929c5d8c937',
                'form' => array(
                    'action' => 'http://upload.qiniup.com/',
                    'file_param_key' => 'key',
                    'params' => array(
                        'token' => '6uEqT5vwuQIM4p1vkf7bk6GHshgNofDb_SYHvabL:kkqkbNLMm2S4lO6rnunwx3AKBQo=:eyJzY29wZSI6ImZhY2UtZGI6ZmFjZVwvbGlic1wvdlBiMTZkNEw5WUZtOW1xbHZUeW9DbzBZNW9nMXZaTFwvNWNiYWIwZTE5YzNiNGVmZmJmMjllOTI5YzVkOGM5MzciLCJkZWFkbGluZSI6MTUzNjg5MDk5MSwidXBIb3N0cyI6WyJodHRwOlwvXC91cC5xaW5pdS5jb20iLCJodHRwOlwvXC91cGxvYWQucWluaXUuY29tIiwiLUggdXAucWluaXUuY29tIGh0dHA6XC9cLzE4My4xMzEuNy4xOCJdfQ==',
                        'key' => 'face/libs/vPb16d4L9YFm9mqlvTyoCo0Y5og1vZL/5cbab0e19c3b4effbf29e929c5d8c937',
                    ),
                ),
            ),
            'updated_at' => '2018-09-12T16:40:51+08:00',
            'created_at' => '2018-09-12T16:40:51+08:00',
            'expired_at' => '2018-09-12T16:50:51+08:00',
        );
    }

    private function getAiService()
    {
        return new AiService($this->auth);
    }
}
