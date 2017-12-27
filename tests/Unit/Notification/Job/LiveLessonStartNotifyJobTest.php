<?php

namespace Tests\Unit\Notification\Job;

use Biz\BaseTestCase;
use Biz\Notification\Job\LiveLessonStartNotifyJob;

class LiveLessonStartNotifyJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'getTask',
                'returnValue' => array('id' => 1, 'title' => 'testTask', 'activityId' => 1, 'courseId' => 1),
            ),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('id' => 1, 'startTime' => time() + 3600),
            ),
        ));

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'findClassroomsByCourseId',
                'returnValue' => array(1 => array('id' => 1)),
            ),
        ));

        $this->mockBiz('IM:ConversationService', array(
            array(
                'functionName' => 'getConversationByTarget',
                'returnValue' => array('no' => '1234567890'),
            ),
        ));

        $this->mockBiz('Queue:QueueService', array(
            array(
                'functionName' => 'pushJob',
                'returnValue' => null,
            ),
        ));

        $job = new LiveLessonStartNotifyJob(array('args' => array('targetType' => 'liveLesson', 'targetId' => 1)), $this->getBiz());

        $result = $job->execute();

        $this->assertTrue($result);
    }

    public function testExecuteWithEmptyClassroom()
    {
        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'getTask',
                'returnValue' => array('id' => 1, 'title' => 'testTask', 'activityId' => 1, 'courseId' => 1),
            ),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('id' => 1, 'startTime' => time() + 3600),
            ),
        ));

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'findClassroomsByCourseId',
                'returnValue' => array(),
            ),
        ));

        $this->mockBiz('IM:ConversationService', array(
            array(
                'functionName' => 'getConversationByTarget',
                'returnValue' => array('no' => '1234567890'),
            ),
        ));

        $this->mockBiz('Queue:QueueService', array(
            array(
                'functionName' => 'pushJob',
                'returnValue' => null,
            ),
        ));

        $job = new LiveLessonStartNotifyJob(array('args' => array('targetType' => 'liveLesson', 'targetId' => 1)), $this->getBiz());

        $result = $job->execute();

        $this->assertTrue($result);
    }
}
