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
			'admin.operate.vip_member' => 'onOperateVipMember'
		);
    }

	public function onOperateVipMember(ServiceEvent $event)
	{
		$vipMember = $event->getSubject();
		$this->getUserService()->updateUserUpdatedTime($vipMember['userId']);
	}

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }
}
