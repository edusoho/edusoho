<?php

namespace Biz\Classroom\Event;

use Biz\Classroom\Service\ClassroomService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClassroomMemberEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'course.task.finish' => 'onTaskFinish',
            'course_member.finished' => 'onCourseMemberFinished',
            'course.members.finish_data_refresh' => 'onCourseMembersFinishedRefresh',
        ];
    }

    public function onCourseMembersFinishedRefresh(Event $event)
    {
        $course = $event->getSubject();
        $updatedMembers = $event->getArgument('updatedMembers');
    }

    public function onCourseMemberFinished(Event $event)
    {
        $member = $event->getSubject();
        $course = $event->getArgument('course');
        if (empty($course['parentId'])) {
        }
    }

    public function onTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $user = $event->getArgument('user');

        $classroom = $this->getClassroomService()->getClassroomByCourseId($taskResult['courseId']);
        if (empty($classroom)) {
            return;
        }

        $this->getClassroomService()->updateLearndNumByClassroomIdAndUserId($classroom['id'], $user['id']);
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }
}
