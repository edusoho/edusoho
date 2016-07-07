<?php
namespace Topxia\Service\User\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Util\EdusohoTuiClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'user.service.registered' => 'onUserRegistered',
            'user.service.follow'     => 'onUserFollowed',
            'user.service.unfollow'   => 'onUserUnfollowed'
        );
    }

    public function onUserRegistered(ServiceEvent $event)
    {
        $user      = $event->getSubject();
        $tuiClient = new EdusohoTuiClient();
        $result    = $tuiClient->addStudent($user);
        $this->sendRegisterMessage($user);
    }

    public function onUserFollowed(ServiceEvent $event)
    {
        $friend = $event->getSubject();
        $user   = $this->getUserService()->getUser($friend['fromId']);

        $message = array(
            'userId'   => $user['id'],
            'userName' => $user['nickname'],
            'opration' => 'follow'
        );
        $this->getNotificationService()->notify($friend['toId'], 'user-follow', $message);

        $message = array(
            'fromId'  => $friend['fromId'],
            'toId'    => $friend['toId'],
            'type'    => 'text',
            'title'   => '好友添加',
            'content' => $user['nickname'].'添加你为好友',
            'custom'  => json_encode(array(
                'fromId'       => $friend['fromId'],
                'nickname'     => $user['nickname'],
                'typeBusiness' => 'verified'
            ))
        );
        $tuiClient = new EdusohoTuiClient();
        $result    = $tuiClient->sendMessage($message);
    }

    public function onUserUnfollowed(ServiceEvent $event)
    {
        $friend = $event->getSubject();
        $user   = $this->getUserService()->getUser($friend['fromId']);

        $message = array(
            'userId'   => $user['id'],
            'userName' => $user['nickname'],
            'opration' => 'unfollow'
        );
        $this->getNotificationService()->notify($friend['toId'], 'user-follow', $message);
    }

    private function sendRegisterMessage($user)
    {
        $senderUser = array();
        $auth       = $this->getSettingService()->get('auth', array());

        if (empty($auth['welcome_enabled'])) {
            return;
        }

        if ($auth['welcome_enabled'] != 'opened') {
            return;
        }

        if (empty($auth['welcome_sender'])) {
            return;
        }

        $senderUser = $this->getUserService()->getUserByNickname($auth['welcome_sender']);

        if (empty($senderUser)) {
            return;
        }

        $welcomeBody = $this->getWelcomeBody($user);

        if (empty($welcomeBody)) {
            return true;
        }

// TODO

//if (strlen($welcomeBody) >= 1000) {

//    $welcomeBody = $this->getWebExtension()->plainTextFilter($welcomeBody, 1000);

//}
        if ($senderUser['id'] != $user['id']) {
            $this->getMessageService()->sendMessage($senderUser['id'], $user['id'], $welcomeBody);
            $conversation = $this->getMessageService()->getConversationByFromIdAndToId($user['id'], $senderUser['id']);
            $this->getMessageService()->deleteConversation($conversation['id']);
        }
    }

    protected function getWelcomeBody($user)
    {
        $site              = $this->getSettingService()->get('site', array());
        $valuesToBeReplace = array('{{nickname}}', '{{sitename}}', '{{siteurl}}');
        $valuesToReplace   = array($user['nickname'], $site['name'], $site['url']);

        $auth = $this->getSettingService()->get('auth', array());
        if (!empty($auth) && isset($auth['welcome_body'])) {
            $welcomeBody = $auth['welcome_body'];
        }

        $welcomeBody = str_replace($valuesToBeReplace, $valuesToReplace, $welcomeBody);
        return $welcomeBody;
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function getMessageService()
    {
        return ServiceKernel::instance()->createService('User.MessageService');
    }

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User.NotificationService');
    }

// undefined container, comment out

//  protected function getWebExtension()

// {

//     return $this->container->get('topxia.twig.web_extension');
    // }
}
