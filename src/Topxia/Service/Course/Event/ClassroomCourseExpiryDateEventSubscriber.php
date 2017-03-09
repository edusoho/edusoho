<?php
namespace Topxia\Service\Course\Event;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Taxonomy\TagOwnerManager;

class ClassroomCourseExpiryDateEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'classroom.update'                 => 'onClassroomUpdate',
            'classroom.member.deadline.update' => 'onClassroomMemberDeadlineUpdate'
        );
    }

    public function onClassroomUpdate(ServiceEvent $event)
    {
        $arguments = $event->getSubject();
        $classroom = $arguments['classroom'];
        $fields    = $arguments['fields'];
        try {
            $this->getConnection()->beginTransaction();

            if (!empty($fields['expiryMode'])) {
                if ($this->canUpdateCoursesExpiryDate($classroom, $fields['expiryMode'])) {
                    $this->updateCoursesExpiryDate($classroom['id'], array(
                        'expiryMode'  => $fields['expiryMode'],
                        'expiryValue' => $fields['expiryValue']
                    ));
                }

                if ($this->canUpdateCoursesMembersDeadline($classroom, $fields['expiryMode'])) {
                    $this->updateCoursesStudentsDeadline($classroom['id'], array(
                        'expiryValue' => $fields['expiryValue'],
                        'expiryMode'  => $fields['expiryMode']
                    ));
                }
            }

            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    public function onClassroomMemberDeadlineUpdate(ServiceEvent $event)
    {
        $arguments   = $event->getSubject();
        $deadline    = $arguments['deadline'];
        $userId      = $arguments['userId'];
        $classroomId = $arguments['classroomId'];

        $this->getCourseService()->updateMemberDeadlineByClassroomIdAndUserId($classroomId, $userId, $deadline);
    }

    protected function canUpdateCoursesExpiryDate($classroom, $expiryMode)
    {
        if ($classroom['status'] == 'draft') {
            return true;
        }

        if ($expiryMode == $classroom['expiryMode']) {
            return true;
        }

        return false;
    }

    protected function canUpdateCoursesMembersDeadline($classroom, $expiryMode)
    {
        if ($expiryMode == $classroom['expiryMode'] && $expiryMode != 'days') {
            return true;
        }

        return false;
    }

    protected function updateCoursesStudentsDeadline($classroomId, $fields)
    {
        if ($fields['expiryMode'] == 'date') {
            $this->getCourseService()->updateMembersDeadlineByClassroomId($classroomId, $fields['expiryValue']);
        }
    }

    protected function updateCoursesExpiryDate($classroomId, $expiryDate)
    {
        $activeCourses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);

        foreach ($activeCourses as $course) {
            $this->getCourseDao()->updateCourse(
                $course['id'],
                array(
                    'expiryMode' => $expiryDate['expiryMode'],
                    'expiryDay'  => $expiryDate['expiryValue']
                )
            );
        }
    }

    protected function getCourseDao()
    {
        return ServiceKernel::instance()->createDao('Course.CourseDao');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getConnection()
    {
        return ServiceKernel::instance()->getConnection();
    }
}
