<?php

namespace Biz\IM\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConversationEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.delete' => 'onCourseDelete',
            'classroom.delete' => 'onClassroomDelete',
        );
    }

    public function onCourseDelete(Event $event)
    {
        $course = $event->getSubject();

        $this->getConversationService()->deleteConversationByTargetIdAndTargetType($course['id'], 'course');
        $this->getConversationService()->deleteMembersByTargetIdAndTargetType($course['id'], 'course');

        return true;
    }

    public function onClassroomDelete(Event $event)
    {
        $classroom = $event->getSubject();

        $this->getConversationService()->deleteConversationByTargetIdAndTargetType($classroom['id'], 'classroom');
        $this->getConversationService()->deleteMembersByTargetIdAndTargetType($classroom['id'], 'classroom');
    }

    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getConversationService()
    {
        return $this->getBiz()->service('IM:ConversationService');
    }
}
