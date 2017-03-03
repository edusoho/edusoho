<?php
namespace Topxia\Service\Course\Event;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Taxonomy\TagOwnerManager;

class ClassroomCourseEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'classroom.update' => 'onClassroomUpdate',
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
                if ($this->canUpdateCourses($classroom, $fields['expiryMode'])) {
                    $this->updateCoursesExpiryDate($classroom['id'], array('expiryMode' => $fields['expiryMode'], 'expiryValue' => $fields['expiryValue']));
                }

                if ($this->canUpdateCoursesMembers($classroom, $fields['expiryMode'])) {
                    $this->updateCoursesStudentsDeadline($classroom['id'], array('expiryValue' => $fields['expiryValue'], 'expiryMode' => $fields['expiryMode'], 'classroomStatus' => $classroom['status']));
                }
            }

            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    protected function canUpdateCourses($classroom, $expiryMode)
    {
        if ($classroom['status'] == 'draft') {
            return true;
        }

        if ($expiryMode == $classroom['expiryMode']) {
            return true;
        }

        return false;
    }

    protected function canUpdateCoursesMembers($classroom, $expiryMode)
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
