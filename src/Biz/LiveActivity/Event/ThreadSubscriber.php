<?php

namespace Biz\LiveActivity\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ThreadSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.thread.create'      => 'onThreadCreate',
            'course.thread.post.create' => 'onPostCreate'
        );
    }

    public function onThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        $this->getLogger('ThreadSubscriber')->debug('onThreadCreate : ', $thread);
        $this->triggerActivitiesByCourseId($thread['courseId']);
    }

    public function onPostCreate(Event $event)
    {
        $post = $event->getSubject();
        $this->getLogger('ThreadSubscriber')->debug('onPostCreate : ', $event->getSubject());
        $this->triggerActivitiesByCourseId($post['courseId']);
    }

    protected function triggerActivitiesByCourseId($courseId)
    {
        $activities = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'discuss');
        if (empty($activities)) {
            return;
        }
        foreach ($activities as $activity) {
            $this->getActivityService()->trigger($activity['id'], 'finish');
        }
    }

    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    protected function getLogger($name)
    {
        $biz = $this->getBiz();
        return $biz['logger'];
    }
}
