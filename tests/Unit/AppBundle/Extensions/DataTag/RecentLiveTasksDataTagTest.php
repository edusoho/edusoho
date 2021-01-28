<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\RecentLiveTasksDataTag;

class RecentLiveTasksDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountMissing()
    {
        $datatag = new RecentLiveTasksDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountError()
    {
        $datatag = new RecentLiveTasksDataTag();
        $datatag->getData(array('count' => 101));
    }

    public function testGetData()
    {
        $fields1 = array(
            'title' => 'task1 title',
            'courseId' => 1,
            'fromCourseSetId' => 1,
            'seq' => 1,
            'activityId' => 1,
            'type' => 'live',
            'startTime' => time() + 3600,
            'endTime' => time() + 3600 * 1,
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
            'status' => 'published',
        );
        $task1 = $this->getTaskDao()->create($fields1);

        $fields2 = array(
            'title' => 'task1 title',
            'courseId' => 1,
            'fromCourseSetId' => 1,
            'seq' => 2,
            'activityId' => 2,
            'type' => 'live',
            'startTime' => time() + 3600 * 1,
            'endTime' => time() + 3600 * 2,
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
            'status' => 'published',
        );
        $task2 = $this->getTaskDao()->create($fields2);

        $fields3 = array(
            'title' => 'task1 title',
            'courseId' => 2,
            'fromCourseSetId' => 2,
            'seq' => 3,
            'activityId' => 3,
            'type' => 'live',
            'startTime' => time() + 3600 * 2,
            'endTime' => time() + 3600 * 3,
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
            'status' => 'published',
        );
        $task3 = $this->getTaskDao()->create($fields3);

        $fields4 = array(
            'title' => 'task1 title',
            'courseId' => 3,
            'fromCourseSetId' => 3,
            'seq' => 4,
            'activityId' => 4,
            'type' => 'live',
            'startTime' => time() + 3600 * 3,
            'endTime' => time() + 3600 * 4,
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
            'status' => 'published',
        );
        $task4 = $this->getTaskDao()->create($fields4);

        $fields5 = array(
            'title' => 'task1 title',
            'courseId' => 11,
            'fromCourseSetId' => 10,
            'seq' => 5,
            'activityId' => 5,
            'type' => 'live',
            'startTime' => time() + 3600 * 4,
            'endTime' => time() + 3600 * 5,
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
        );
        $task5 = $this->getTaskDao()->create($fields5);

        $datatag = new RecentLiveTasksDataTag();
        $tasks = $datatag->getData(array('count' => 5));
        $this->assertEquals(4, count($tasks));

        $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'findStudentMemberByUserId',
                'returnValue' => array(array('id' => 1, 'courseId' => 1), array('id' => 2, 'courseId' => 3)),
            ),
        ));

        $tasks = $datatag->getData(array('count' => 5, 'userId' => 1));
        $this->assertEquals(3, count($tasks));
    }

    public function testGetDataEmpty()
    {
        $datatag = new RecentLiveTasksDataTag();
        $tasks = $datatag->getData(array('count' => 5, 'userId' => 1));
        $this->assertEmpty($tasks);
    }

    private function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }
}
