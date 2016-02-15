<?php
namespace Topxia\Service\User\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\WebBundle\Util\TargetHelper;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Util\EdusohoTuiClient;

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
        $largeAvatar = empty($user['largeAvatar']) ? '' : $this->getFileService()->parseFileUri($user['largeAvatar']);

        $message['title'] = $user['nickname'];
        $host = 'http://'.$_SERVER['HTTP_HOST'];
        $message['custom'] = json_encode(array(
            'fromId' => $message['fromId'],
            'nickname' => $user['nickname'],
            'imgUrl' => empty($largeAvatar) ? $host.'/assets/img/default/avatar.png' : $host.'/files/'.$largeAvatar['path'],
            'typeMsg' => $message['type'],
            'typeBusiness' => in_array('ROLE_TEACHER', $user['roles']) ? 'teacher' : 'friend',
            'createdTime' => time()
        ));
        $tuiClient = new EdusohoTuiClient();
        $result = $tuiClient->sendMessage($message);
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    public function getFileService()
    {
        return ServiceKernel::instance()->createService('Content.FileService');
    }
}
