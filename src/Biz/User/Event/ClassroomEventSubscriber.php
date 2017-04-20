<?php

namespace Biz\User\Event;

use AppBundle\Common\StringToolkit;
use Biz\User\Service\StatusService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Classroom\Service\ClassroomService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClassroomEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * @return mixed
     */
    public static function getSubscribedEvents()
    {
        return array(
            'classroom.join' => 'onClassroomJoin',
            'classroom.auditor_join' => 'onClassroomGuest',
        );
    }

    public function onClassroomJoin(Event $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');

        $this->publishJoinStatus($classroom, $userId, 'become_student');
        $this->syncCourseStudents($classroom, $userId);
    }

    public function onClassroomGuest(Event $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');
        // publish status
        $this->publishJoinStatus($classroom, $userId, 'become_auditor');
        //add user to classroom courses
        // $this->syncCourseStudents($classroom, $userId);
    }

    private function simplifyClassroom($classroom)
    {
        return array(
            'id' => $classroom['id'],
            'title' => $classroom['title'],
            'picture' => $classroom['middlePicture'],
            'about' => StringToolkit::plain($classroom['about'], 100),
            'price' => $classroom['price'],
        );
    }

    private function syncCourseStudents($classroom, $userId)
    {
        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
        if (empty($courses)) {
            return;
        }

        foreach ($courses as $course) {
            $member = $this->getMemberService()->getCourseMember($course['id'], $userId);
            if (empty($member)) {
                $this->getMemberService()->becomeStudentByClassroomJoined($course['id'], $userId);
            }
        }
    }

    private function publishJoinStatus($classroom, $userId, $type)
    {
        $status = array(
            'type' => $type,
            'classroomId' => $classroom['id'],
            'objectType' => 'classroom',
            'objectId' => $classroom['id'],
            'private' => $classroom['status'] == 'published' ? 0 : 1,
            'userId' => $userId,
            'properties' => array(
                'classroom' => $this->simplifyClassroom($classroom),
            ),
        );

        $status['private'] = $classroom['showable'] == 1 ? $status['private'] : 1;
        $this->getStatusService()->publishStatus($status);
    }

    /**
     * @return StatusService
     */
    protected function getStatusService()
    {
        return $this->getBiz()->service('User:StatusService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
