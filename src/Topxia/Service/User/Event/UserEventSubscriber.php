<?php
namespace Topxia\Service\User\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\WebBundle\Util\TargetHelper;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class UserEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'user.service.registered' => 'onUserRegistered',
            'user.service.follow' => 'onUserFollowed'
        );
    }

    public function onUserRegistered(ServiceEvent $event)
    {
        $user = $event->getSubject();
        $this->getEduCloudService()->addStudent($user);
    }

    public function onUserFollowed(ServiceEvent $event)
    {
        $friend = $event->getSubject();
        $user = $this->getUserService()->getUser($friend['fromId']);
        $message = array(
            'fromId' => $friend['fromId'],
            'toId' => $friend['toId'],
            'type' => 'text',
            'title' => '好友添加',
            'content' => $user['nickname'].'添加你为好友',
            'custom' => json_encode(array(
                'fromId' => $friend['fromId'],
                'nickname' => $user['nickname'],
                'typeBusiness' => 'verified'
            ))
        );
        $this->getEduCloudService()->sendMessage($message);
    }


    private function getEduCloudService()
    {
        return ServiceKernel::instance()->createService('EduCloud.EduCloudService');
    }

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }
}
