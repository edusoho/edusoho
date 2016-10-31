<?php
namespace Topxia\Service\User\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'user.registered'   => 'onUserRegistered',
            'user.follow'     => 'onUserFollowed',
            'user.unfollow'   => 'onUserUnfollowed'
        );
    }

    public function onUserRegistered(ServiceEvent $event)
    {
        $user = $event->getSubject();
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
}
