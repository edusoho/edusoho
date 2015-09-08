<?php
namespace Classroom\Service\Classroom\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class ClassroomEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'classroom.join' => 'onClassroomJoin',
            'classroom.auditor_join' => 'onClassroomGuest',
        );
    }

    public function onClassroomJoin(ServiceEvent $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');
        $this->getStatusService()->publishStatus(array(
            'type' => 'become_student',
            'classroomId' => $classroom['id'],
            'objectType' => 'classroom',
            'objectId' => $classroom['id'],
            'private' => $classroom['status'] == 'published' ? 0 : 1,
            'private' =>$classroom['showable'] == 1 ? 0 : 1,
            'userId' => $userId,
            'properties' => array(
                'classroom' => $this->simplifyClassroom($classroom),
            ),
        ));
    }

    public function onClassroomGuest(ServiceEvent $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');
        $this->getStatusService()->publishStatus(array(
            'type' => 'become_auditor',
            'classroomId' => $classroom['id'],
            'objectType' => 'classroom',
            'objectId' => $classroom['id'],
            'private' => $classroom['status'] == 'published' ? 0 : 1,
            'private' =>$classroom['showable'] == 1 ? 0 : 1,
            'userId' => $userId,
            'properties' => array(
                'classroom' => $this->simplifyClassroom($classroom),
            ),
        ));
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

    private function getStatusService()
    {
        return ServiceKernel::instance()->createService('User.StatusService');
    }
}
