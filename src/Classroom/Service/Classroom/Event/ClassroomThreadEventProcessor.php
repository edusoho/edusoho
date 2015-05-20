<?php
namespace Classroom\Service\Classroom\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class ClassroomThreadEventProcessor
{

    public function onThreadCreate(ServiceEvent $event)
    {
        $thread = $event->getSubject();
        $this->getClassroomService()->waveClassroom($thread['targetId'], 'threadNum', +1);
    }

    public function onThreadDelete(ServiceEvent $event)
    {
        $thread = $event->getSubject();
        $this->getClassroomService()->waveClassroom($thread['targetId'], 'threadNum', -1);
        $this->getClassroomService()->waveClassroom($thread['targetId'], 'postNum', 0-$thread['postNum']);
    }

    public function onPostCreate(ServiceEvent $event)
    {
        $post = $event->getSubject();
        $this->getClassroomService()->waveClassroom($post['targetId'], 'postNum', +1);
    }

    public function onPostDelete(ServiceEvent $event)
    {
        $post = $event->getSubject();
        $this->getClassroomService()->waveClassroom($post['targetId'], 'postNum', 0-$event->getArgument('deleted'));
    }

    private function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }
}