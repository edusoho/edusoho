<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\EliteCourseThreadsByTypeDataTag;

class EliteCourseThreadsByTypeDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'findCoursesByIds',
                'returnValue' => array(1 => array('id' => 1), 2 => array('id' => 2)),
            ),
        ));
        $this->mockBiz('Course:CourseSetService', array(
            array(
                'functionName' => 'findCourseSetsByCourseIds',
                'returnValue' => array(1 => array('id' => 1), 2 => array('id' => 2)),
            ),
        ));
        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'findUsersByIds',
                'withParams' => array(array(1)),
                'returnValue' => array(1 => array('id' => 1)),
            ),
            array(
                'functionName' => 'findUsersByIds',
                'withParams' => array(array(1, 1)),
                'returnValue' => array(1 => array('id' => 1)),
            ),
        ));
        $thread1 = $this->getThreadDao()->create(array('title' => 'thread1 title', 'content' => 'thread1 content', 'type' => 'question', 'courseId' => 1, 'userId' => 1, 'courseSetId' => 1, 'isElite' => 1, 'latestPostUserId' => 1));
        $thread2 = $this->getThreadDao()->create(array('title' => 'thread2 title', 'content' => 'thread2 content', 'type' => 'question', 'courseId' => 1, 'userId' => 1, 'courseSetId' => 1, 'latestPostUserId' => 1));
        $thread3 = $this->getThreadDao()->create(array('title' => 'thread3 title', 'content' => 'thread3 content', 'type' => 'discussion', 'courseId' => 1, 'userId' => 1, 'courseSetId' => 1, 'isElite' => 1, 'latestPostUserId' => 1));
        $thread4 = $this->getThreadDao()->create(array('title' => 'thread4 title', 'content' => 'thread4 content', 'type' => 'discussion', 'courseId' => 1, 'userId' => 1, 'courseSetId' => 1, 'latestPostUserId' => 1));
        $datatag = new EliteCourseThreadsByTypeDataTag();
        //1.count异常情况
        $arguments = array();
        $hasException = false;
        try {
            $datatag->getData($arguments);
        } catch (\Exception $e) {
            $hasException = true;
        }
        $this->assertTrue($hasException);

        $arguments = array('count' => 101);
        try {
            $datatag->getData($arguments);
        } catch (\Exception $e) {
            $hasException = true;
        }
        $this->assertTrue($hasException);

        //2.正常访问
        //2.1.无type
        $arguments = array(
            'count' => 10,
        );
        $result = $datatag->getData($arguments);
        $this->assertEquals('thread1 title', $result[0]['title']);
        $this->assertEquals('thread3 title', $result[1]['title']);
        $this->assertEquals(2, count($result));

        //2.2.有type
        $arguments = array(
            'count' => 10,
            'type' => 'discussion',
        );
        $result = $datatag->getData($arguments);
        $this->assertEquals('thread3 title', $result[0]['title']);
        $this->assertEquals(1, count($result));
    }

    private function getThreadDao()
    {
        return $this->createDao('Course:ThreadDao');
    }
}
