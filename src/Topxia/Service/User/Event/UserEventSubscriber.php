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
        );
    }

    public function onUserRegistered(ServiceEvent $event)
    {
        $user = $event->getSubject();
        $this->getEduCloudService()->addStudent($user);
    }

    private function getEduCloudService()
    {
        return ServiceKernel::instance()->createService('EduCloud.EduCloudService');
    }
}
