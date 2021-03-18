<?php

namespace Tests\Unit\Classroom\Event;

use Biz\BaseTestCase;
use Biz\Classroom\Event\ClassroomMemberEventSubscriber;

class ClassroomMemberEventSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals([
            'course.task.finish' => 'onTaskFinish',
            'course_member.finished' => 'onCourseMemberFinished',
            'course.members.finish_data_refresh' => 'onCourseMembersFinishedRefresh',
            'classroom.course.delete' => 'onClassroomCourseDelete',
        ], ClassroomMemberEventSubscriber::getSubscribedEvents());
    }
}
