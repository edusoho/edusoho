<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\TaskByActivityDataTag;

class TaskByActivityDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testGetDataCourseIdEmpty()
    {
        $datatag = new TaskByActivityDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testGetDataActivityEmpty()
    {
        $datatag = new TaskByActivityDataTag();
        $datatag->getData(array('courseId' => 1));
    }

    public function testGetData()
    {
        $task = array(
            'id' => 1,
            'title' => 'task name',
        );
        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'getTaskByCourseIdAndActivityId',
                'returnValue' => $task,
            ),
        ));

        $datatag = new TaskByActivityDataTag();
        $result = $datatag->getData(array('courseId' => 1, 'activityId' => 2));
        $this->assertArrayEquals($task, $result);
    }
}
