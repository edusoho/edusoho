<?php

namespace Biz\Course\Event;

use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClassroomCourseExpiryDateEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'classroom.update' => 'onClassroomUpdate',
            'classroom.member.deadline.update' => 'onClassroomMemberDeadlineUpdate',
        );
    }

    public function onClassroomUpdate(Event $event)
    {
        $arguments = $event->getSubject();
        $classroom = $arguments['classroom'];
        $fields = $arguments['fields'];
        $db = $this->getBiz()->offsetGet('db');
        try {
            $db->beginTransaction();

            if (!empty($fields['expiryMode'])) {
                if ($this->canUpdateCoursesExpiryDate($classroom, $fields['expiryMode'])) {
                    $this->updateCoursesExpiryDate($classroom['id'], array(
                        'expiryMode' => $fields['expiryMode'],
                        'expiryValue' => $fields['expiryValue'],
                    ));
                }
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function onClassroomMemberDeadlineUpdate(Event $event)
    {
        $arguments = $event->getSubject();
        $deadline = $arguments['deadline'];
        $userId = $arguments['userId'];
        $classroomId = $arguments['classroomId'];

        $this->getCourseMemberService()->updateMemberDeadlineByClassroomIdAndUserId($classroomId, $userId, $deadline);
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
            $this->getCourseMemberService()->updateMembersDeadlineByClassroomId($classroomId, $fields['expiryValue']);
        }
    }

    protected function updateCoursesExpiryDate($classroomId, $expiryDate)
    {
        $activeCourses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);

        foreach ($activeCourses as $course) {
            $this->getCourseDao()->update($course['id'], $this->getCourseService()->buildCourseExpiryDataFromClassroom($expiryDate['expiryMode'], $expiryDate['expiryValue']));
        }
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->getBiz()->dao('Course:CourseDao');
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
    protected function getCourseMemberService()
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
