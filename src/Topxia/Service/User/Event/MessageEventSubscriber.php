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
        $largeAvatar = empty($user['largeAvatar']) ? '' : $this->getFileService()->parseFileUri($user['largeAvatar']);

        $message['title'] = $user['nickname'];
        $message['custom'] = json_encode(array(
            'fromId' => $message['fromId'],
            'nickname' => $user['nickname'],
            'imgUrl' => empty($largeAvatar) ? '' : 'files/'.$largeAvatar['path'],
            'typeObject' => in_array('ROLE_TEACHER', $user['roles']) ? 'teacher' : 'friend',
            'typeMsg' => $message['type'],
            'typeBusiness' => 'normal',
            'createdTime' => time()
        ));
        $this->getEduCloudService()->sendMessage($message);
    }

    protected function getEduCloudService()
    {
        return ServiceKernel::instance()->createService('EduCloud.EduCloudService');
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
