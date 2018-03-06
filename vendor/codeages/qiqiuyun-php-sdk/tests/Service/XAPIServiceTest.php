<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\XAPIService;
use QiQiuYun\SDK\XAPIActivityTypes;
use QiQiuYun\SDK\XAPIObjectTypes;

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

    public function testSearchCourse()
    {
        $actor = $this->getActor();
        $object = array(
            'id' => '/cloud/search?q=单反&type=course',
            'definitionType' => XAPIActivityTypes::COURSE
        );
        $result = array(
            'response' => '单反'
        );

        $httpClient = $this->mockHttpClient(array(
            'actor' => $actor,
            'object' => $object,
            'result' => $result,
        ));
        $service = $this->createXAPIService($httpClient);
        $statement = $service->searched($actor, $object, $result);

        $this->assertEquals($actor, $statement['actor']);
        $this->assertEquals($object, $statement['object']);
        $this->assertEquals($result, $statement['result']);
    }

    public function testSearchTeacher()
    {
        $actor = $this->getActor();
        $object = array(
            'id' => '/cloud/search?q=李老师&type=teacher',
            'objectType' => XAPIObjectTypes::AGENT
        );
        $result = array(
            'response' => '李老师'
        );

        $httpClient = $this->mockHttpClient(array(
            'actor' => $actor,
            'object' => $object,
            'result' => $result,
        ));
        $service = $this->createXAPIService($httpClient);
        $statement = $service->searched($actor, $object, $result);

        $this->assertEquals($actor, $statement['actor']);
        $this->assertEquals($object, $statement['object']);
        $this->assertEquals($result, $statement['result']);
    }

    public function testLogged()
    {
        $actor = $this->getActor();
        $object = array(
            'id' => '网校accessKey',
            'name' => 'ABC摄影网',
            'definitionType' => XAPIActivityTypes::APPLICATION
        );
        $httpClient = $this->mockHttpClient(array(
            'actor' => $actor,
            'object' => $object,
        ));
        $service = $this->createXAPIService($httpClient);
        $statement = $service->logged($actor, $object);

        $this->assertEquals($actor, $statement['actor']);
        $this->assertEquals($object, $statement['object']);
    }

    public function testPurchased()
    {
        $actor = $this->getActor();
        $object = array(
            'id' => '38983',
            'name' => '摄影基础',
            'definitionType' => XAPIActivityTypes::COURSE,
        );
        $result = array(
            'amount' => '199.99'
        );
        $httpClient = $this->mockHttpClient(array(
            'actor' => $actor,
            'object' => $object,
            'result' => $result,
        ));

        $service = $this->createXAPIService($httpClient);
        $statement = $service->purchased($actor, $object, $result);

        $this->assertEquals($actor, $statement['actor']);
        $this->assertEquals($object, $statement['object']);
        $this->assertEquals($result, $statement['result']);
    }

    private function getActor()
    {
        return array(
            'account' => array(
                'id' => '28923',
                'name' => '张三',
                'email' => 'zhangsan@howzhi.com',
                'homePage' => 'http://www.example.com',
                'phone' => '13588888888'
            )
        );
    }

    protected function createXAPIService($httpClient = null)
    {
        return new XAPIService($this->auth, array(
            'school_name' => '测试网校',
        ), null, $httpClient);
    }
}
