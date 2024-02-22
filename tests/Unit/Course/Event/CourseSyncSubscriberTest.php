<?php

namespace Tests\Unit\Course\Event;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Course\Event\CourseSyncSubscriber;
use Codeages\Biz\Framework\Event\Event;

class CourseSyncSubscriberTest extends BaseTestCase
{
    public function testOnCourseSetUpdate()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);
        $event = new Event(
            [
                'parentId' => 0,
                'id' => 1,
                'title' => 'test Title',
            ]
        );

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'findCoursesByCourseSetId', 'returnValue' => [['id' => 1, 'title' => 'test']]],
            [
                'functionName' => 'findCoursesByCourseSetIds',
                'returnValue' => [
                    ['id' => 1],
                ],
            ],
        ]);
        $mockCourseSetDao = $this->mockBiz('Course:CourseSetDao', [
            ['functionName' => 'findCourseSetsByParentIdAndLocked', 'returnValue' => [['id' => 1]]],
            ['functionName' => 'update', 'returnValue' => ['id' => 1, 'title' => 'testTitle']],
        ]);
        $mockCourseDao = $this->mockBiz('Course:CourseDao', [
            ['functionName' => 'update', 'returnValue' => []],
        ]);

        $subscriber->onCourseSetUpdate($event);
        $mockCourseSetDao->shouldHaveReceived('update');
        $mockCourseDao->shouldHaveReceived('update');
    }

    public function testUpdateCopiedCourses()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);

        $mockCourseDao = $this->mockBiz('Course:CourseDao', [
            ['functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => [['id' => 1]]],
            ['functionName' => 'update', 'returnValue' => []],
        ]);

        ReflectionUtils::invokeMethod($subscriber, 'updateCopiedCourses', [['id' => 1]]);
        $mockCourseDao->shouldHaveReceived('update');
    }

    public function testOnCourseTeachersChange()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);
        $event = new Event(
            [
                'parentId' => 1,
            ],
            [
                'teachers' => [
                    ['id' => 1, 'isVisible' => 1],
                ],
            ]
        );
        $result = $subscriber->onCourseTeachersChange($event);
        $this->assertNull($result);

        $event = new Event(
            [
                'parentId' => 0,
                'id' => 1,
                'title' => 'test Title',
            ],
            [
                'teachers' => [
                    ['id' => 1, 'isVisible' => 1],
                ],
            ]
        );

        $this->mockBiz('Course:CourseDao', [
            ['functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => [['id' => 1, 'courseSetId' => 1], ['id' => 2]]],
            ['functionName' => 'update', 'returnValue' => [['id' => 1, 'courseSetId' => 1]]],
        ]);
        $mockClassroomService = $this->mockBiz('Classroom:ClassroomService', [
            ['functionName' => 'getClassroomByCourseId', 'returnValue' => ['id' => 1], 'withParams' => [1]],
            ['functionName' => 'getClassroomByCourseId', 'returnValue' => null, 'withParams' => [2]],
            ['functionName' => 'updateClassroomTeachers', 'returnValue' => []],
        ]);
        $this->mockBiz('Course:CourseMemberDao', [
            ['functionName' => 'findByCourseIdAndRole', 'returnValue' => [['id' => 1]]],
            ['functionName' => 'getByCourseIdAndUserId', 'returnValue' => ['id' => 1]],
            ['functionName' => 'delete', 'returnValue' => []],
            ['functionName' => 'create', 'returnValue' => ['isVisible' => 1, 'userId' => 1]],
        ]);

        $subscriber->onCourseTeachersChange($event);
        $mockClassroomService->shouldHaveReceived('updateClassroomTeachers');
    }

    public function testOnCourseChapterCreate()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);
        $event = new Event(
            [
                'copyId' => 1,
            ]
        );
        $result = $subscriber->onCourseChapterCreate($event);
        $this->assertNull($result);
    }

    public function testOnCourseChapterUpdate()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);
        $event = new Event(
            [
                'copyId' => 1,
            ]
        );
        $result = $subscriber->onCourseChapterUpdate($event);
        $this->assertNull($result);
    }

    public function testOnCourseChapterDelete()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);
        $event = new Event(
            [
                'copyId' => 1,
            ]
        );
        $result = $subscriber->onCourseChapterDelete($event);
        $this->assertNull($result);
    }

    public function testOnCourseMaterialUpdate()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);
        $event = new Event(
            [
                'copyId' => 1,
            ]
        );
        $result = $subscriber->onCourseMaterialUpdate($event);
        $this->assertNull($result);

        $event = new Event(
            [
                'copyId' => 0,
                'courseId' => 1,
                'id' => 1,
            ]
        );
        $this->mockBiz('Course:CourseDao', [
            ['functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => [['id' => 1]]],
        ]);
        $mockCourseMaterialDao = $this->mockBiz('Course:CourseMaterialDao', [
            ['functionName' => 'findByCopyIdAndLockedCourseIds', 'returnValue' => [['id' => 1]]],
            ['functionName' => 'update', 'returnValue' => [['id' => 1]]],
        ]);

        $subscriber->onCourseMaterialUpdate($event);
        $mockCourseMaterialDao->shouldHaveReceived('update');
    }

    public function testOnCourseMaterialDelete()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);
        $event = new Event(
            [
                'copyId' => 1,
            ]
        );
        $result = $subscriber->onCourseMaterialDelete($event);
        $this->assertNull($result);

        $event = new Event(
            [
                'copyId' => 0,
                'courseId' => 1,
                'id' => 1,
            ]
        );
        $this->mockBiz('Course:CourseDao', [
            ['functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => [['id' => 1]]],
        ]);
        $mockCourseMaterialDao = $this->mockBiz('Course:CourseMaterialDao', [
            ['functionName' => 'findByCopyIdAndLockedCourseIds', 'returnValue' => [['id' => 1]]],
            ['functionName' => 'batchDelete', 'returnValue' => []],
        ]);

        $subscriber->onCourseMaterialDelete($event);
        $mockCourseMaterialDao->shouldHaveReceived('batchDelete');
    }
}
