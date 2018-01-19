<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\FreeTasksDataTag;

class FreeTasksDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $fields1 = array(
            'title' => 'task1 title',
            'courseId' => 1,
            'fromCourseSetId' => 1,
            'seq' => 1,
            'activityId' => 1,
            'type' => 'text',
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
        );
        $task1 = $this->getTaskDao()->create($fields1);

        $fields2 = array(
            'title' => 'task1 title',
            'courseId' => 1,
            'fromCourseSetId' => 1,
            'seq' => 2,
            'activityId' => 2,
            'type' => 'text',
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
        );
        $task2 = $this->getTaskDao()->create($fields2);

        $fields3 = array(
            'title' => 'task1 title',
            'courseId' => 1,
            'fromCourseSetId' => 1,
            'seq' => 3,
            'activityId' => 3,
            'type' => 'text',
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
        );
        $task3 = $this->getTaskDao()->create($fields3);

        $fields4 = array(
            'title' => 'task1 title',
            'courseId' => 1,
            'fromCourseSetId' => 1,
            'seq' => 4,
            'activityId' => 4,
            'type' => 'text',
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
        );
        $task4 = $this->getTaskDao()->create($fields4);

        $fields5 = array(
            'title' => 'task1 title',
            'courseId' => 1,
            'fromCourseSetId' => 1,
            'seq' => 5,
            'activityId' => 5,
            'type' => 'text',
            'mode' => '',
            'isFree' => 1,
            'createdUserId' => 1,
        );
        $task5 = $this->getTaskDao()->create($fields5);

        $datatag = new FreeTasksDataTag();
        $datas = $datatag->getData(array());
        $this->assertEquals(4, count($datas));

        $datas = $datatag->getData(array('count' => 5));
        $this->assertEquals(5, count($datas));
    }

    private function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }
}
