<?php
namespace Topxia\Service\User\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.thread.post.create' => 'onThreadPostCreate'
        );
    }

    public function onThreadPostCreate(ServiceEvent $event)
    {
        $post = $event->getSubject();

        $thread = $this->getThreadService()->getThread($post['courseId'], $post['threadId']);

        $isTeacher = $this->getCourseService()->isCourseTeacher($post['courseId'], $post['userId']);

        $course = $this->getCourseService()->getCourse($post['courseId']);

        if ($isTeacher && $thread['type'] == 'question') {
            $this->getStatusService()->publishStatus(array(
                'courseId'   => $post['courseId'],
                'type'       => 'teacher_thread_post',
                'objectType' => 'thread_post',
                'objectId'   => $post['id'],
                'private'    => $course['status'] == 'published' ? 0 : 1,
                'properties' => array(
                    'thread' => $thread,
                    'post'   => $post
                )
            ));
        }
    }

    protected function getStatusService()
    {
        return ServiceKernel::instance()->createService('User.StatusService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getThreadService()
    {
        return ServiceKernel::instance()->createService('Course.ThreadService');
    }
}
