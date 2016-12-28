<?php


namespace Biz\User\Event;


use Biz\User\Service\StatusService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;

class ClassroomEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * @return mixed
     */
    public static function getSubscribedEvents()
    {
        return array(
            'classroom.join'         => 'onClassroomJoin',
            'classroom.auditor_join' => 'onClassroomGuest',
        );
    }

    public function onClassroomJoin(Event $event)
    {
        $classroom         = $event->getSubject();
        $userId            = $event->getArgument('userId');
        $status            = array(
            'type'        => 'become_student',
            'classroomId' => $classroom['id'],
            'objectType'  => 'classroom',
            'objectId'    => $classroom['id'],
            'private'     => $classroom['status'] == 'published' ? 0 : 1,
            'userId'      => $userId,
            'properties'  => array(
                'classroom' => $this->simplifyClassroom($classroom)
            )
        );
        $status['private'] = $classroom['showable'] == 1 ? $status['private'] : 1;

        $this->getStatusService()->publishStatus($status);
    }

    public function onClassroomGuest(Event $event)
    {
        $classroom = $event->getSubject();
        $userId    = $event->getArgument('userId');
        $status    = array(
            'type'        => 'become_auditor',
            'classroomId' => $classroom['id'],
            'objectType'  => 'classroom',
            'objectId'    => $classroom['id'],
            'private'     => $classroom['status'] == 'published' ? 0 : 1,
            'userId'      => $userId,
            'properties'  => array(
                'classroom' => $this->simplifyClassroom($classroom)
            )
        );

        $status['private'] = $classroom['showable'] == 1 ? $status['private'] : 1;
        $this->getStatusService()->publishStatus($status);
    }

    private function simplifyClassroom($classroom)
    {
        return array(
            'id'      => $classroom['id'],
            'title'   => $classroom['title'],
            'picture' => $classroom['middlePicture'],
            'about'   => StringToolkit::plain($classroom['about'], 100),
            'price'   => $classroom['price']
        );
    }

    /**
     * @return StatusService
     */
    protected function getStatusService()
    {
        return $this->getBiz()->service('User:StatusService');
    }
}