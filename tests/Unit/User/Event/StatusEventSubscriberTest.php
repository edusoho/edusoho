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
        $event = new Event([
            'courseId' => 123,
            'courseTaskId' => 12,
        ]);
        $result = $subscriber->onCourseTaskStart($event);
        $this->assertNull($result);

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'getCourse', 'withParams' => [123], 'returnValue' => [
                'id' => 123,
                'status' => 'published',
                'parentId' => 1,
                'courseSetId' => 44,
                'title' => 'test Title',
                'summary' => '123',
                'type' => 'live',
                'rating' => 0.1,
                'price' => 0.01,
            ]],
        ]);
        $this->mockBiz('Course:MemberService', [
            ['functionName' => 'isCourseStudent', 'returnValue' => ['id' => 123]],
        ]);
        $result = $subscriber->onCourseTaskStart($event);
        $this->assertNull($result);

        $this->mockBiz('Task:TaskService', [
            ['functionName' => 'getTask', 'returnValue' => [
                'id' => 123,
                'number' => 12,
                'type' => 'lesson',
                'title' => 'Task Title',
            ]],
        ]);
        $this->mockBiz('Classroom:ClassroomService', [
            ['functionName' => 'getClassroomByCourseId', 'returnValue' => ['showable' => 1, 'id' => 1]],
        ]);
        $mockStatusService = $this->mockBiz('User:StatusService', [
            ['functionName' => 'publishStatus', 'returnValue' => []],
        ]);
        $this->mockBiz('Course:CourseSetService', [
            ['functionName' => 'getCourseSet', 'returnValue' => ['cover' => 'file://web/sdg.jpg']],
        ]);
        $subscriber->onCourseTaskStart($event);
        $mockStatusService->shouldHaveReceived('publishStatus');
    }

    public function testOnCourseTaskFinish()
    {
        $subscriber = new StatusEventSubscriber($this->biz);
        $event = new Event(
            [
                'courseId' => 123,
                'courseTaskId' => 12,
            ],
            [
                'user' => ['id' => 333],
            ]
        );
        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'getCourse', 'withParams' => [123], 'returnValue' => [
                'id' => 123,
                'status' => 'published',
                'parentId' => 0,
                'courseSetId' => 44,
                'title' => 'test Title',
                'summary' => '123',
                'type' => 'live',
                'rating' => 0.1,
                'price' => 0.01,
            ]],
        ]);
        $this->mockBiz('Course:MemberService', [
            ['functionName' => 'isCourseStudent', 'returnValue' => ['id' => 123]],
        ]);
        $result = $subscriber->onCourseTaskFinish($event);
        $this->assertNull($result);

        $this->mockBiz('Task:TaskService', [
            ['functionName' => 'getTask', 'returnValue' => [
                'id' => 123,
                'number' => 12,
                'type' => 'lesson',
                'title' => 'Task Title',
            ]],
        ]);
        $mockStatusService = $this->mockBiz('User:StatusService', [
            ['functionName' => 'publishStatus', 'returnValue' => []],
        ]);
        $this->mockBiz('Course:CourseSetService', [
            ['functionName' => 'getCourseSet', 'returnValue' => ['cover' => 'file://web/sdg.jpg']],
        ]);
        $subscriber->onCourseTaskFinish($event);
        $mockStatusService->shouldHaveReceived('publishStatus');
    }
}
