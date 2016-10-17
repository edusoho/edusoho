<?php
namespace Topxia\Service\User\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'user.follow'     => 'onUserFollowed',
            'user.unfollow'   => 'onUserUnfollowed'
        );
    }

    public function onUserFollowed(ServiceEvent $event)
    {
        $friend = $event->getSubject();
        $user   = $this->getUserService()->getUser($friend['fromId']);

        $message = array(
            'userId'   => $user['id'],
            'userName' => $user['nickname'],
            'opration' => 'follow'
        );
        $this->getNotificationService()->notify($friend['toId'], 'user-follow', $message);
    }

    public function onUserUnfollowed(ServiceEvent $event)
    {
        $friend = $event->getSubject();
        $user   = $this->getUserService()->getUser($friend['fromId']);

        $message = array(
            'userId'   => $user['id'],
            'userName' => $user['nickname'],
            'opration' => 'unfollow'
        );
        $this->getNotificationService()->notify($friend['toId'], 'user-follow', $message);
    }

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User.NotificationService');
    }
}
