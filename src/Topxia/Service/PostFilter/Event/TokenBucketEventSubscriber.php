<?php
namespace Topxia\Service\PostFilter\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class TokenBucketEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'thread.before_create' => 'before',
            'thread.create' => 'incrToken',

            'thread.post.before_create' => 'before',
            'thread.post.create' => 'incrToken',

            'course.thread.before_create' => 'before',
            'course.thread.create' => 'incrToken',

            'course.thread.post.before_create' => 'before',
            'course.thread.post.create' => 'incrToken',

            'group.thread.before_create' => 'before',
            'group.thread.create' => 'incrToken',

            'group.thread.post.before_create' => 'before',
            'group.thread.post.create' => 'incrToken',
        );
    }

    public function before(ServiceEvent $event)
    {
        $currentUser = ServiceKernel::instance()->getCurrentUser();
        if ($currentUser->isAdmin() || $currentUser->isSuperAdmin() || $currentUser->isTeacher()) {
            return;
        }

        $currentIp = $currentUser->currentIp;

        if (!($this->getTokenBucketService()->hasToken($currentIp, 'thread')
            && $this->getTokenBucketService()->hasToken($currentUser['id'], 'threadLoginedUser'))) {
            $event->stopPropagation();
        }

    }

    public function incrToken(ServiceEvent $event)
    {

        $currentUser = ServiceKernel::instance()->getCurrentUser();
        if ($currentUser->isAdmin() || $currentUser->isSuperAdmin() || $currentUser->isTeacher()) {
            return;
        }

        $currentIp = $currentUser->currentIp;

        $this->getTokenBucketService()->incrToken($currentIp, 'thread');
        $this->getTokenBucketService()->incrToken($currentUser['id'], 'threadLoginedUser');
    }

    public function getTokenBucketService()
    {
        return ServiceKernel::instance()->createService('PostFilter.TokenBucketService');
    }
}
