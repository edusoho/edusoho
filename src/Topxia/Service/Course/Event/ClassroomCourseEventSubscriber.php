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

            if (!empty($fields['expiryMode']) && !empty($fields['expiryValue'])) {
                if ($this->canUpdateCoursesMembersAndCourses($classroom)) {
                    $this->updateClassroomCoursesExpiryDate($classroom['id'], array('expiryMode' => $fields['expiryMode'], 'expiryValue' => $fields['expiryValue']));

                    $this->updateClassroomCoursesStudentsExpiryDate($classroom['id'], array('expiryValue' => $fields['expiryValue'], 'expiryMode' => $fields['expiryMode'], 'classroomStatus' => $classroom['status']));
                }
            }

            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    protected function buildMemberDeadline($fields, $member)
    {
        if ($fields['classroomStatus'] == 'published' && $fields['expiryMode'] == 'days') {
            $fields['expiryValue'] = $member['deadline'];
        }

        if ($fields['classroomStatus'] == 'draft' && $fields['expiryMode'] == 'days') {
            $fields['expiryValue'] = $member['createdTime'] + $fields['expiryValue'] * 24 * 60 * 60;
        }

        return $fields['expiryValue'];
    }

    protected function canUpdateCoursesMembersAndCourses($classroom)
    {
        if ($classroom['status'] == 'draft') {
            return true;
        }

        return false;
    }

    protected function updateClassroomCoursesStudentsExpiryDate($classroomId, $fields)
    {
        $members = $this->getCourseService()->searchMembers(
            array('classroomId' => $classroomId), 
            array('createdTime', 'desc'), 
            0, 
            PHP_INT_MAX
        );

        foreach ($members as $member) {
            $deadline = $this->buildMemberDeadline($fields, $member);

            $this->getCourseService()->updateCourseMember($member['id'], array('deadline' => $deadline));
        }
    }

    protected function updateClassroomCoursesExpiryDate($classroomId, $expiryDate)
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
