<?php

namespace Tests\Unit\Notification\Job;

use Biz\BaseTestCase;
use Biz\Notification\Job\PushNotificationOneHourJob;

class PushNotificationOneHourJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'getTask',
                'returnValue' => array('id' => 1, 'title' => 'testTask', 'type' => 'video', 'activityId' => 1, 'courseId' => 1, 'fromCourseSetId' => 1),
            ),
        ));

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 1, 'title' => 'test Title', 'smallPicture' => '/a/b/c.jpg'),
            ),
        ));

        $this->mockBiz('Course:CourseSetService', array(
            array(
                'functionName' => 'getCourseSet',
                'returnValue' => array('id' => 1),
            ),
        ));

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('convNo' => '1234567890'),
            ),
        ));

        $this->mockBiz('Queue:QueueService', array(
            array(
                'functionName' => 'pushJob',
                'returnValue' => null,
            ),
        ));

        $job = new PushNotificationOneHourJob(array('args' => array('targetType' => 'lesson', 'targetId' => 1)), $this->getBiz());
        $result = $job->execute();

        $this->assertNull($result);
    }
}
