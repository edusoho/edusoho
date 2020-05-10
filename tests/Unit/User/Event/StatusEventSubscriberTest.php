<?php

namespace Tests\Unit\User\Event;

use Biz\BaseTestCase;
use Biz\User\Event\StatusEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class StatusEventSubscriberTest extends BaseTestCase
{
    public function testOnCourseTaskStart()
    {
        $subscriber = new StatusEventSubscriber($this->biz);
        $event = new Event(array(
            'courseId' => 123,
            'courseTaskId' => 12,
        ));
        $result = $subscriber->onCourseTaskStart($event);
        $this->assertNull($result);

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'withParams' => array(123), 'returnValue' => array(
                'id' => 123,
                'status' => 'published',
                'parentId' => 1,
                'courseSetId' => 44,
                'title' => 'test Title',
                'summary' => '123',
                'type' => 'live',
                'rating' => 0.1,
                'price' => 0.01,
            )),
        ));
        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'isCourseStudent', 'returnValue' => array('id' => 123)),
        ));
        $result = $subscriber->onCourseTaskStart($event);
        $this->assertNull($result);

        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'getTask', 'returnValue' => array(
                'id' => 123,
                'number' => 12,
                'type' => 'lesson',
                'title' => 'Task Title',
            )),
        ));
        $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'getClassroomByCourseId', 'returnValue' => array('showable' => 1, 'id' => 1)),
        ));
        $mockStatusService = $this->mockBiz('User:StatusService', array(
            array('functionName' => 'publishStatus', 'returnValue' => array()),
        ));
        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'getCourseSet', 'returnValue' => array('cover' => 'file://web/sdg.jpg')),
        ));
        $subscriber->onCourseTaskStart($event);
        $mockStatusService->shouldHaveReceived('publishStatus');
    }

    public function testOnCourseTaskFinish()
    {
        $subscriber = new StatusEventSubscriber($this->biz);
        $event = new Event(
            array(
                'courseId' => 123,
                'courseTaskId' => 12,
            ),
            array(
                'user' => array('id' => 333),
            )
        );
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'withParams' => array(123), 'returnValue' => array(
                'id' => 123,
                'status' => 'published',
                'parentId' => 0,
                'courseSetId' => 44,
                'title' => 'test Title',
                'summary' => '123',
                'type' => 'live',
                'rating' => 0.1,
                'price' => 0.01,
            )),
        ));
        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'isCourseStudent', 'returnValue' => array('id' => 123)),
        ));
        $result = $subscriber->onCourseTaskFinish($event);
        $this->assertNull($result);

        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'getTask', 'returnValue' => array(
                'id' => 123,
                'number' => 12,
                'type' => 'lesson',
                'title' => 'Task Title',
            )),
        ));
        $mockStatusService = $this->mockBiz('User:StatusService', array(
            array('functionName' => 'publishStatus', 'returnValue' => array()),
        ));
        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'getCourseSet', 'returnValue' => array('cover' => 'file://web/sdg.jpg')),
        ));
        $subscriber->onCourseTaskFinish($event);
        $mockStatusService->shouldHaveReceived('publishStatus');
    }
}
