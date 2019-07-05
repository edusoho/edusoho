<?php

namespace Tests\Unit\User\Event;

use Biz\BaseTestCase;
use Biz\User\Event\CourseThreadEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class CourseThreadEventSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvent()
    {
        $this->assertEquals(array(
            'course.thread.post.create' => 'onThreadPostCreate',
        ), CourseThreadEventSubscriber::getSubscribedEvents());
    }

    public function testOnThreadPostCreateWithCourseParentId()
    {
        $this->mockBiz('Course:ThreadService', array(
            array(
                'functionName' => 'getThread',
                'returnValue' => array(
                    'userId' => 1,
                    'type' => 'question',
                ),
            ),
        ));

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array(
                    'id' => 1,
                    'parentId' => 1,
                    'status' => 'published',
                ),
            ),
        ));

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'findClassroomIdsByCourseId',
                'returnValue' => array(
                    array(
                        'classroomId' => 1,
                    ),
                ),
            ),
            array(
                'functionName' => 'isClassroomTeacher',
                'returnValue' => true,
            ),
        ));

        $this->mockBiz('User:StatusService', array(
            array(
                'functionName' => 'publishStatus',
                'times' => 1,
            ),
        ));

        $event = new Event(array(
            'id' => 1,
            'courseId' => 1,
            'threadId' => 1,
            'userId' => 1,
        ));

        $eventSubscriber = new CourseThreadEventSubscriber($this->biz);
        $eventSubscriber->onThreadPostCreate($event);

        $this->createService('User:StatusService')->shouldHaveReceived('publishStatus');
    }

    public function testOnThreadPostCreateWithoutParentId()
    {
        $this->mockBiz('Course:ThreadService', array(
            array(
                'functionName' => 'getThread',
                'returnValue' => array(
                    'userId' => 1,
                    'type' => 'question',
                ),
            ),
        ));

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array(
                    'id' => 1,
                    'parentId' => 0,
                    'status' => 'published',
                ),
            ),
        ));

        $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'isCourseTeacher',
                'returnValue' => true,
            ),
        ));

        $this->mockBiz('User:StatusService', array(
            array(
                'functionName' => 'publishStatus',
                'times' => 1,
            ),
        ));

        $event = new Event(array(
            'id' => 1,
            'courseId' => 1,
            'threadId' => 1,
            'userId' => 1,
        ));

        $eventSubscriber = new CourseThreadEventSubscriber($this->biz);
        $eventSubscriber->onThreadPostCreate($event);

        $this->createService('User:StatusService')->shouldHaveReceived('publishStatus');
    }
}
