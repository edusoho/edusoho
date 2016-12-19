<?php
namespace Topxia\Service\OpenCourse\Event;

use Codeages\Biz\Framework\Event\Event;
use Topxia\Service\Common\ServiceKernel;

class OpenCourseThreadEventProcessor
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
        return ServiceKernel::instance()->createService('OpenCourse.OpenCourseService');
    }

    private function getThreadService()
    {
        return ServiceKernel::instance()->createDao('Thread.ThreadService');
    }
}
