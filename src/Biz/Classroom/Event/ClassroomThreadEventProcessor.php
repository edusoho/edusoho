<?php

namespace Biz\Classroom\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;

class ClassroomThreadEventProcessor extends EventSubscriber
{
    public function onThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        $this->getClassroomService()->waveClassroom($thread['targetId'], 'threadNum', +1);
    }

    public function onThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        $this->getClassroomService()->waveClassroom($thread['targetId'], 'threadNum', -1);
        $this->getClassroomService()->waveClassroom($thread['targetId'], 'postNum', 0 - $thread['postNum']);
    }

    public function onPostCreate(Event $event)
    {
        $post = $event->getSubject();
        $this->getClassroomService()->waveClassroom($post['targetId'], 'postNum', +1);

        $isTeacher = $this->getClassroomService()->isClassroomTeacher($post['targetId'], $post['userId']);

        if ($isTeacher) {
            $this->getThreadService()->setPostAdopted($post['id']);
            $this->getThreadService()->setThreadSolved($post['threadId']);
        }
    }

    public function onPostDelete(Event $event)
    {
        $post = $event->getSubject();
        $this->getClassroomService()->waveClassroom($post['targetId'], 'postNum', 0 - $event->getArgument('deleted'));

        $adoptedPostCount = $this->getThreadService()->searchPostsCount(array(
            'threadId' => $post['threadId'],
            'adopted' => 1,
        ));

        if (empty($adoptedPostCount)) {
            $this->getThreadService()->cancelThreadSolved($post['threadId']);
        }
    }

    private function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    private function getThreadService()
    {
        return $this->getBiz()->service('Thread:ThreadService');
    }
}
