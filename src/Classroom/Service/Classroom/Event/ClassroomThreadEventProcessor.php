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
        $this->getClassroomService()->waveClassroom($thread['targetId'], 'postNum', 0 - $thread['postNum']);
    }

    public function onPostCreate(ServiceEvent $event)
    {
        $post = $event->getSubject();
        $this->getClassroomService()->waveClassroom($post['targetId'], 'postNum', +1);

        $isTeacher = $this->getClassroomService()->isClassroomTeacher($post['targetId'], $post['userId']);

        if ($isTeacher) {
            $this->getThreadService()->setPostAdopted($post['id']);
            $this->getThreadService()->setThreadSolved($post['threadId']);
        }
    }

    public function onPostDelete(ServiceEvent $event)
    {
        $post = $event->getSubject();
        $this->getClassroomService()->waveClassroom($post['targetId'], 'postNum', 0 - $event->getArgument('deleted'));

        $adoptedPostCount = $this->getThreadService()->searchPostsCount(array(
            'threadId' => $post['threadId'],
            'adopted'  => 1
        ));

        if (empty($adoptedPostCount)) {
            $this->getThreadService()->cancelThreadSolved($post['threadId']);
        }
    }

    private function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getThreadService()
    {
        return ServiceKernel::instance()->createDao('Thread.ThreadService');
    }
}
