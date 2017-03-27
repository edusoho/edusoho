<?php
namespace Topxia\Service\IM\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConversationEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.delete'    => 'onCourseDelete',
            'classroom.delete' => 'onClassroomDelete'
        );
    }

    public function onCourseDelete(ServiceEvent $event)
    {
        $course = $event->getSubject();

        $this->getConversationService()->deleteConversationByTargetIdAndTargetType($course['id'], 'course');
        $this->getConversationService()->deleteMembersByTargetIdAndTargetType($course['id'], 'course');

        return true;
    }

    public function onClassroomDelete(ServiceEvent $event)
    {
        $classroom = $event->getSubject();

        $this->getConversationService()->deleteConversationByTargetIdAndTargetType($classroom['id'], 'classroom');
        $this->getConversationService()->deleteMembersByTargetIdAndTargetType($classroom['id'], 'classroom');
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getConversationService()
    {
        return ServiceKernel::instance()->createService('IM.ConversationService');
    }
}
