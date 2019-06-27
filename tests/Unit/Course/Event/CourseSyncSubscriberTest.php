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
            array(
                'parentId' => 0,
                'id' => 1,
                'title' => 'test Title',
            )
        );

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'findCoursesByCourseSetId', 'returnValue' => array(array('id' => 1))),
        ));
        $mockCourseSetDao = $this->mockBiz('Course:CourseSetDao', array(
            array('functionName' => 'findCourseSetsByParentIdAndLocked', 'returnValue' => array(array('id' => 1))),
            array('functionName' => 'update', 'returnValue' => array()),
        ));
        $subscriber->onCourseSetUpdate($event);
        $mockCourseSetDao->shouldHaveReceived('update');
    }

    public function testUpdateCopiedCourses()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);

        $mockCourseDao = $this->mockBiz('Course:CourseDao', array(
            array('functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => array(array('id' => 1))),
            array('functionName' => 'update', 'returnValue' => array()),
        ));

        ReflectionUtils::invokeMethod($subscriber, 'updateCopiedCourses', array(array('id' => 1)));
        $mockCourseDao->shouldHaveReceived('update');
    }

    public function testOnCourseTeachersChange()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);
        $event = new Event(
            array(
                'parentId' => 1,
            ),
            array(
                'teachers' => array(
                    array('id' => 1, 'isVisible' => 1),
                ),
            )
        );
        $result = $subscriber->onCourseTeachersChange($event);
        $this->assertNull($result);

        $event = new Event(
            array(
                'parentId' => 0,
                'id' => 1,
                'title' => 'test Title',
            ),
            array(
                'teachers' => array(
                    array('id' => 1, 'isVisible' => 1),
                ),
            )
        );

        $this->mockBiz('Course:CourseDao', array(
            array('functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => array(array('id' => 1, 'courseSetId' => 1), array('id' => 2))),
            array('functionName' => 'update', 'returnValue' => array(array('id' => 1, 'courseSetId' => 1))),
        ));
        $mockClassroomService = $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'getClassroomByCourseId', 'returnValue' => array('id' => 1), 'withParams' => array(1)),
            array('functionName' => 'getClassroomByCourseId', 'returnValue' => null, 'withParams' => array(2)),
            array('functionName' => 'updateClassroomTeachers', 'returnValue' => array()),
        ));
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findByCourseIdAndRole', 'returnValue' => array(array('id' => 1))),
            array('functionName' => 'getByCourseIdAndUserId', 'returnValue' => array('id' => 1)),
            array('functionName' => 'delete', 'returnValue' => array()),
            array('functionName' => 'create', 'returnValue' => array('isVisible' => 1, 'userId' => 1)),
        ));

        $subscriber->onCourseTeachersChange($event);
        $mockClassroomService->shouldHaveReceived('updateClassroomTeachers');
    }

    public function testOnCourseChapterCreate()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);
        $event = new Event(
            array(
                'copyId' => 1,
            )
        );
        $result = $subscriber->onCourseChapterCreate($event);
        $this->assertNull($result);
    }

    public function testOnCourseChapterUpdate()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);
        $event = new Event(
            array(
                'copyId' => 1,
            )
        );
        $result = $subscriber->onCourseChapterUpdate($event);
        $this->assertNull($result);
    }

    public function testOnCourseChapterDelete()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);
        $event = new Event(
            array(
                'copyId' => 1,
            )
        );
        $result = $subscriber->onCourseChapterDelete($event);
        $this->assertNull($result);
    }

    public function testOnCourseMaterialUpdate()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);
        $event = new Event(
            array(
                'copyId' => 1,
            )
        );
        $result = $subscriber->onCourseMaterialUpdate($event);
        $this->assertNull($result);

        $event = new Event(
            array(
                'copyId' => 0,
                'courseId' => 1,
                'id' => 1,
            )
        );
        $this->mockBiz('Course:CourseDao', array(
            array('functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => array(array('id' => 1))),
        ));
        $mockCourseMaterialDao = $this->mockBiz('Course:CourseMaterialDao', array(
            array('functionName' => 'findByCopyIdAndLockedCourseIds', 'returnValue' => array(array('id' => 1))),
            array('functionName' => 'update', 'returnValue' => array(array('id' => 1))),
        ));

        $subscriber->onCourseMaterialUpdate($event);
        $mockCourseMaterialDao->shouldHaveReceived('update');
    }

    public function testOnCourseMaterialDelete()
    {
        $subscriber = new CourseSyncSubscriber($this->biz);
        $event = new Event(
            array(
                'copyId' => 1,
            )
        );
        $result = $subscriber->onCourseMaterialDelete($event);
        $this->assertNull($result);

        $event = new Event(
            array(
                'copyId' => 0,
                'courseId' => 1,
                'id' => 1,
            )
        );
        $this->mockBiz('Course:CourseDao', array(
            array('functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => array(array('id' => 1))),
        ));
        $mockCourseMaterialDao = $this->mockBiz('Course:CourseMaterialDao', array(
            array('functionName' => 'findByCopyIdAndLockedCourseIds', 'returnValue' => array(array('id' => 1))),
            array('functionName' => 'delete', 'returnValue' => array()),
        ));

        $subscriber->onCourseMaterialDelete($event);
        $mockCourseMaterialDao->shouldHaveReceived('delete');
    }
}
