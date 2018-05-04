<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\XAPIService;
use QiQiuYun\SDK\Constants\XAPIActivityTypes;
use QiQiuYun\SDK\Constants\XAPIObjectTypes;

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
            'definitionType' => XAPIActivityTypes::SEARCH_ENGINE
        );
        $result = array(
            'response' => '单反',
            'type' => 'course',
        );

        $httpClient = $this->mockHttpClient(array(
            'actor' => $actor,
            'object' => $object,
            'result' => $result,
        ));
        $service = $this->createXAPIService($httpClient);
        $statement = $service->searched($actor, $object, $result, null, null, false);

        $this->assertEquals('https://w3id.org/xapi/acrossx/verbs/searched', $statement['verb']['id']);
        $this->assertEquals('https://w3id.org/xapi/acrossx/activities/search-engine',
            $statement['object']['definition']['type']);
        $this->assertEquals('http://adlnet.gov/expapi/activities/course',
            $statement['result']['extensions']['https://w3id.org/xapi/acrossx/extensions/type']);
        $this->assertEquals('单反', $statement['result']['response']);
    }

    public function testSearchTeacher()
    {
        $actor = $this->getActor();
        $object = array(
            'id' => '/cloud/search?q=李老师&type=teacher',
            'definitionType' => XAPIActivityTypes::SEARCH_ENGINE
        );
        $result = array(
            'response' => '李老师',
            'type' => 'user-profile'
        );

        $httpClient = $this->mockHttpClient(array(
            'actor' => $actor,
            'object' => $object,
            'result' => $result,
        ));
        $service = $this->createXAPIService($httpClient);
        $statement = $service->searched($actor, $object, $result, null, null, false);

        $this->assertEquals('http://id.tincanapi.com/activitytype/user-profile',
            $statement['result']['extensions']['https://w3id.org/xapi/acrossx/extensions/type']);
        $this->assertEquals('李老师', $statement['result']['response']);
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
        $statement = $service->logged($actor, $object, null, null, null, false);

        $this->assertEquals('https://w3id.org/xapi/adl/verbs/logged-in', $statement['verb']['id']);
        $this->assertEquals('http://activitystrea.ms/schema/1.0/application',
            $statement['object']['definition']['type']);
    }

    public function testPurchased()
    {
        $actor = $this->getActor();
        $object = array(
            'id' => '38983',
            'name' => '摄影基础',
            'definitionType' => XAPIActivityTypes::CLASS_ONLINE,
        );
        $result = array(
            'amount' => 199.99
        );
        $httpClient = $this->mockHttpClient(array(
            'actor' => $actor,
            'object' => $object,
            'result' => $result,
        ));

        $service = $this->createXAPIService($httpClient);
        $statement = $service->purchased($actor, $object, $result, null, null, false);

        $this->assertEquals('http://activitystrea.ms/schema/1.0/purchase', $statement['verb']['id']);
        $this->assertEquals('https://w3id.org/xapi/acrossx/activities/class-online',
            $statement['object']['definition']['type']);
        $this->assertEquals(199.99, $statement['result']['extensions']['http://xapi.edusoho.com/extensions/amount']);
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
            'school_name' => '测试网校', 'school_url' => 'http://demo.edusoho.com'
        ), null, $httpClient);
    }
}
