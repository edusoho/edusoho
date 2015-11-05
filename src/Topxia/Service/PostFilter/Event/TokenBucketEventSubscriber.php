<?php
namespace Topxia\Service\PostFilter\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Common\ArrayToolkit;
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

            'courseThread.before_create' => 'before',
            'courseThread.create' => 'incrToken',

            'courseThread.post.before_ceate' => 'before',
            'courseThread.post.create' => 'incrToken',

            'groupThread.before_create' => 'before',
            'groupThread.create' => 'incrToken',

            'groupThread.post.before_ceate' => 'before',
            'groupThread.post.create' => 'incrToken',
        );
    }

    public function before(ServiceEvent $event)
    {
        $currentUser = ServiceKernel::instance()->getCurrentUser();
        $currentIp = $currentUser->currentIp;

        $eventName = $event->getName();
        $names = explode('.', $eventName);

        if(!$this->getTokenBucketService()->hasToken($currentIp, $names[0])
            ||!$this->getTokenBucketService()->hasToken($currentUser['id'], $names[0].'LoginedUser')){
    	   $event->stopPropagation();
        }

    }

    public function incrToken(ServiceEvent $event)
    {
        $eventName = $event->getName();
        $names = explode('.', $eventName);

        $currentUser = ServiceKernel::instance()->getCurrentUser();
        $currentIp = $currentUser->currentIp;

        $this->getTokenBucketService()->incrToken($currentIp, $names[0]);
        $this->getTokenBucketService()->incrToken($currentUser['id'], $names[0].'LoginedUser');
    }

    public function getTokenBucketService()
    {
    	return ServiceKernel::instance()->createService('PostFilter.TokenBucketService');
    }
}
