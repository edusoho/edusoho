<?php

namespace Biz\Course\Event;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Biz\Classroom\Service\ClassroomService;

/**
 * 实现业务：将courseset下第一个course的第一个teacher作为courseSet的teacher
 * 当course新增、删除，course的teachers变更（增删）时触发
 * Class CourseSetTeacherSubscriber.
 */
class CourseSetTeacherSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.create' => 'calculateCourseTeacher',
            'course.delete' => 'onCourseDelete',
            'course.teachers.update' => 'calculateCourseTeacher',
            'course.teacher.create' => 'calculateCourseTeacher',
        );
    }

    public function onCourseDelete(Event $event)
    {
        $this->calculateCourseTeacher($event, true);
    }

    public function calculateCourseTeacher(Event $event, $isDeleteTeacher = false)
    {
        $course = $event->getSubject();
        if (empty($course)) {
            return;
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        if (empty($courseSet)) {
            return;
        }

        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);

        if ($isDeleteTeacher) {
            unset($courses[$course['id']]);
        }

        if (empty($courses)) {
            $firstCourse = $course;
        } else {
            usort($courses, function ($c1, $c2) {
                if ($c1['createdTime'] == $c2['createdTime']) {
                    return 0;
                }

                return $c1['createdTime'] < $c2['createdTime'] ? -1 : 1;
            });
            $firstCourse = $courses[0];
        }

        $teachers = $this->getMemberService()->findCourseTeachers($firstCourse['id']);
        if (empty($teachers)) {
            return;
        }

        usort($teachers, function ($t1, $t2) {
            if ($t1['seq'] == $t2['seq']) {
                return $t1['createdTime'] < $t2['createdTime'] ? -1 : 1;
            }

            return $t1['seq'] < $t2['seq'] ? -1 : 1;
        });

        $this->getCourseSetService()->updateCourseSetTeacherIds($courseSet['id'], array($teachers[0]['userId']));
        $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
        if (!empty($classroom)) {
            $this->getClassroomService()->updateClassroomTeachers($classroom['id']);
        }
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }
}
