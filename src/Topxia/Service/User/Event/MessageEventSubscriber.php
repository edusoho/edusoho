<?php
namespace Topxia\Service\User\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\WebBundle\Util\TargetHelper;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class MessageEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'message.service.send' => 'onMessageSended',
        );
    }

    public function onMessageSended(ServiceEvent $event)
    {
        $message = $event->getSubject();
        
        $this->getEduCloudService()->sendMessage($message);
    }

    private function getEduCloudService()
    {
        return ServiceKernel::instance()->createService('EduCloud.EduCloudService');
    }
}
