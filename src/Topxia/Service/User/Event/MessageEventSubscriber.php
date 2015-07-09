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
        $user = $this->getUserService()->getUser($message['fromId']);
        $message['title'] = '来自'.$user['nickname'].'的私信';
        $message['custom'] = json_encode(array(
            'id' => $message['fromId'],
            'typeBusiness' => 'message'
        ));
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
