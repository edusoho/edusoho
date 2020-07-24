<?php

namespace Tests\Unit\NewComer;

use Biz\NewComer\CourseCreatedTask;
use Biz\BaseTestCase;

class CourseCreatedTaskTest extends BaseTestCase
{
    public function testGetStatusFalseByTask()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                'functionName' => 'get',
                'returnValue' =>
                    ['course_created_task' => ['status' => []]]
                ]
            ]
        );

        $task = new CourseCreatedTask($this->getBiz());
        $result = $task->getStatus();

        $this->assertEquals(false, $result);
    }

    public function testGetStatusTrueByTask()
    {
        $this->mockBiz('System:SettingService',
            [
                [
                'functionName' => 'get',
                'returnValue' =>
                    ['course_created_task' => ['status' => 1]]
                ]
            ]
        );

        $task = new CourseCreatedTask($this->getBiz());
        $result = $task->getStatus();

        $this->assertEquals(true, $result);
    }

    public function testGetStatusTrueByCount()
    {
        $this->mockBiz('Course:CourseSetService',
            [
                [
                    'functionName' => 'countCourseSets',
                    'returnValue' =>
                        ['publishCount' => 1]
                ],
                [
                    'functionName' => 'set',
                    'returnValue' => null
                ]
            ]
        );

        $count = new CourseCreatedTask($this->getBiz());
        $result = $count->getStatus();

        $this->assertEquals(true, $result);
    }
}