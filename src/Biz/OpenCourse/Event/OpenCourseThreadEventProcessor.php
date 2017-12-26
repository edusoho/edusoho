<?php

namespace Biz\OpenCourse\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;

class OpenCourseThreadEventProcessor extends EventSubscriber
{
    public function onPostCreate(Event $event)
    {
        $post = $event->getSubject();
        $this->getOpenCourseService()->waveCourse($post['targetId'], 'postNum', +1);
    }

    public function onPostDelete(Event $event)
    {
        $post = $event->getSubject();
        $this->getOpenCourseService()->waveCourse($post['targetId'], 'postNum', 0 - $event->getArgument('deleted'));
    }

    private function getOpenCourseService()
    {
        return $this->getBiz()->service('OpenCourse:OpenCourseService');
    }
}
