<?php

namespace Biz\User\Event;

use Biz\User\Service\UserService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VipMemberEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'admin.operate.vip_member' => 'onOperateVipMember',
        );
    }

    public function onOperateVipMember(Event $event)
    {
        $vipMember = $event->getSubject();
        $this->getUserService()->updateUserUpdatedTime($vipMember['userId']);
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}
