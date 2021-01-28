<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseThreadsByTypeDataTag;

class CourseThreadsByTypeDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyCount()
    {
        $dataTag = new CourseThreadsByTypeDataTag();
        $announcement = $dataTag->getData(array());
    }

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
                'returnValue' => array(1 => array('id' => 1), 2 => array('id' => 2)),
            ),
        ));

        $thread1 = $this->getThreadDao()->create(array('title' => 'thread1 title', 'content' => 'thread1 content', 'type' => 'question', 'courseId' => 1, 'userId' => 1, 'courseSetId' => 1));
        $thread2 = $this->getThreadDao()->create(array('title' => 'thread2 title', 'content' => 'thread2 content', 'type' => 'question', 'courseId' => 2, 'userId' => 1, 'courseSetId' => 2));
        $thread3 = $this->getThreadDao()->create(array('title' => 'thread3 title', 'content' => 'thread3 content', 'type' => 'discussion', 'courseId' => 1, 'userId' => 2, 'courseSetId' => 1));

        $datatag = new CourseThreadsByTypeDataTag();
        $datas = $datatag->getData(array('count' => 5));
        $this->assertEquals(3, count($datas));

        $datas = $datatag->getData(array('type' => 'question', 'count' => 5));
        $this->assertEquals(2, count($datas));

        $this->assertArrayHasKey('course', $datas[0]);
        $this->assertArrayHasKey('courseSet', $datas[0]);
        $this->assertArrayHasKey('user', $datas[0]);
    }

    private function getThreadDao()
    {
        return $this->createDao('Course:ThreadDao');
    }
}
