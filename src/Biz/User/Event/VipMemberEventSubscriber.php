<?php
namespace Biz\User\Event;

use Biz\User\Service\UserService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Topxia\Service\Common\ServiceEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VipMemberEventSubscriber extends EventSubscriber implements EventSubscriberInterface
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

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
