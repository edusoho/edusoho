<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\XAPIService;

class XAPIServiceTest extends BaseTestCase
{
    public function testWatchVideo_Success()
    {
        $actor = array(
            'id' => 1,
            'name' => '测试用户',
        );
        $object = array(
            'id' => 1,
            'name' => '测试任务',
            'course' => array(
                'id' => 1,
                'title' => '测试课程',
                'description' => '这是一个测试课程',
            ),
            'video' => array(
                'id' => '1111',
                'name' => '测试视频.mp4',
            ),
        );
        $result = array(
            'duration' => 100,
        );

        $httpClient = $this->mockHttpClient(array(
            'actor' => $actor,
            'object' => $object,
            'result' => $result,
        ));

        $service = $this->createXAPIService($httpClient);

        $statement = $service->watchVideo($actor, $object, $result);

        $this->assertArrayHasKey('actor', $statement);
        $this->assertArrayHasKey('object', $statement);
        $this->assertArrayHasKey('result', $statement);
    }

    /**
     * @expectedException \QiQiuYun\SDK\Exception\ResponseException
     * @expectedExceptionCode 9
     */
    public function testWatchVideo_Error()
    {
        $actor = array(
            'id' => 1,
            'name' => '测试用户',
        );
        $object = array(
            'id' => -1,
            'name' => 'error',
            'course' => array(
                'id' => 1,
                'title' => '测试课程',
                'description' => '这是一个测试课程',
            ),
            'video' => array(
                'id' => '1111',
                'name' => '测试视频.mp4',
            ),
        );
        $result = array(
            'duration' => 100,
        );

        $httpClient = $this->mockHttpClient(array(
            'error' => array(
                'code' => 9,
                'message' => 'invalid argument',
            ),
        ));

        $service = $this->createXAPIService($httpClient);
        $statement = $service->watchVideo($actor, $object, $result);
    }

    protected function createXAPIService($httpClient = null)
    {
        return new XAPIService($this->auth, array(
            'school_name' => '测试网校',
        ), null, $httpClient);
    }
}
