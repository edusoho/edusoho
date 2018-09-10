<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\XAPIService;
use QiQiuYun\SDK\Constants\XAPIActivityTypes;

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
                'price' => 100,
                'tags' => '｜摄影｜运动｜',
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
            'definitionType' => XAPIActivityTypes::SEARCH_ENGINE,
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
            'definitionType' => XAPIActivityTypes::SEARCH_ENGINE,
        );
        $result = array(
            'response' => '李老师',
            'type' => 'user-profile',
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
            'definitionType' => XAPIActivityTypes::APPLICATION,
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
            'amount' => 199.99,
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

    public function testRegistered()
    {
        $actor = $this->getActor();

        $httpClient = $this->mockHttpClient(array(
            'actor' => $actor,
        ));

        $service = $this->createXAPIService($httpClient);
        $statement = $service->registered($actor, null, null, null, null, false);

        $this->assertEquals(array(
            'id' => 'http://adlnet.gov/expapi/verbs/registered',
            'display' => array(
                'zh-CN' => '注册了',
                'en-US' => 'registered',
            ),
        ), $statement['verb']);
    }

    public function testRated()
    {
        $actor = $this->getActor();
        $object = array(
            'id' => '38983',
            'name' => '摄影基础',
            'definitionType' => XAPIActivityTypes::COURSE,
            'course' => array(
                'id' => 1,
                'title' => '摄影基础',
                'tags' => '|摄影|光圈|',
                'price' => 99.8,
                'description' => '与摄影相关的知识和操作课程，适合刚入门的摄影爱好者。',
            ),
        );
        $result = array(
            'score' => array(
                'raw' => 4,
                'max' => 5,
                'min' => 0,
            ),
            'response' => '这个是值得购买到课程',
        );
        $httpClient = $this->mockHttpClient(array(
            'actor' => $actor,
            'object' => $object,
            'result' => $result,
        ));

        $service = $this->createXAPIService($httpClient);
        $statement = $service->rated($actor, $object, $result, null, null, false);

        $this->assertEquals(array(
            'id' => 'http://id.tincanapi.com/verb/rated',
            'display' => array(
                'zh-CN' => '评分了',
                'en-US' => 'rated',
            ),
        ), $statement['verb']);

        $this->assertEquals('http://adlnet.gov/expapi/activities/course',
            $statement['object']['definition']['type']);
        $keys = array_keys($statement['object']['definition']['extensions']);
        $this->assertEquals('http://xapi.edusoho.com/extensions/course',
            $keys[0]);
        $this->assertEquals(array('raw' => 4, 'max' => 5, 'min' => 0), $statement['result']['score']);
        $this->assertEquals('这个是值得购买到课程', $statement['result']['response']);
    }

    public function testBookmarked()
    {
        $actor = $this->getActor();
        $object = array(
            'id' => '38983',
            'name' => '摄影基础',
            'definitionType' => XAPIActivityTypes::COURSE,
            'course' => array(
                'id' => 1,
                'title' => '摄影基础',
                'tags' => '|摄影|光圈|',
                'price' => 99.8,
                'description' => '与摄影相关的知识和操作课程，适合刚入门的摄影爱好者。',
            ),
        );

        $httpClient = $this->mockHttpClient(array(
            'actor' => $actor,
            'object' => $object,
        ));

        $service = $this->createXAPIService($httpClient);
        $statement = $service->bookmarked($actor, $object, null, null, null, false);

        $this->assertEquals(array(
            'id' => 'https://w3id.org/xapi/adb/verbs/bookmarked',
            'display' => array(
                'zh-CN' => '收藏了',
                'en-US' => 'bookmarked',
            ),
        ), $statement['verb']);

        $this->assertEquals('http://adlnet.gov/expapi/activities/course',
            $statement['object']['definition']['type']);
        $keys = array_keys($statement['object']['definition']['extensions']);
        $this->assertEquals('http://xapi.edusoho.com/extensions/course',
            $keys[0]);
    }

    public function testShared()
    {
        $actor = $this->getActor();
        $object = array(
            'id' => '38983',
            'name' => '摄影基础',
            'definitionType' => XAPIActivityTypes::CLASS_ONLINE,
        );

        $httpClient = $this->mockHttpClient(array(
            'actor' => $actor,
            'object' => $object,
        ));

        $service = $this->createXAPIService($httpClient);
        $statement = $service->shared($actor, $object, null, null, null, false);

        $this->assertEquals(array(
            'id' => 'http://adlnet.gov/expapi/verbs/shared',
            'display' => array(
                'zh-CN' => '分享了',
                'en-US' => 'shared',
            ),
        ), $statement['verb']);
        $this->assertEquals('https://w3id.org/xapi/acrossx/activities/class-online',
            $statement['object']['definition']['type']);
    }

    private function getActor()
    {
        return array(
            'account' => array(
                'id' => '28923',
                'name' => '张三',
                'email' => 'zhangsan@howzhi.com',
                'homePage' => 'http://www.example.com',
                'phone' => '13588888888',
            ),
        );
    }

    protected function createXAPIService($httpClient = null)
    {
        return new XAPIService($this->auth, array(
            'school_name' => '测试网校',
            'school_url' => 'http://demo.edusoho.com',
            'school_version' => '8.0.0',
        ), null, $httpClient);
    }
}
