<?php
namespace Topxia\Service\User\Event;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Common\ServiceEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VipMemberEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'admin.update_vipMember' => 'updateUserUpdatedTime'
        );
    }

    public function updateUserUpdatedTime(ServiceEvent $event)
    {
        $vipMember = $event->getSubject();
        $this->getUserService()->updateUserUpdatedTime($vipMember['userId'], array());
    }

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }
}
