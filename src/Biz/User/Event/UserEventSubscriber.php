<?php

namespace Biz\User\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'user.registered' => 'onUserRegistered',
            'user.follow' => 'onUserFollowed',
            'user.unfollow' => 'onUserUnfollowed',
        );
    }

    public function onUserRegistered(Event $event)
    {
        $user = $event->getSubject();
        $this->sendRegisterMessage($user);
    }

    public function onUserFollowed(Event $event)
    {
        $friend = $event->getSubject();
        $user = $this->getUserService()->getUser($friend['fromId']);

        $message = array(
            'userId' => $user['id'],
            'userName' => $user['nickname'],
            'opration' => 'follow',
        );
        $this->getNotificationService()->notify($friend['toId'], 'user-follow', $message);
    }

    public function onUserUnfollowed(Event $event)
    {
        $friend = $event->getSubject();
        $user = $this->getUserService()->getUser($friend['fromId']);

        $message = array(
            'userId' => $user['id'],
            'userName' => $user['nickname'],
            'opration' => 'unfollow',
        );
        $this->getNotificationService()->notify($friend['toId'], 'user-follow', $message);
    }

    private function sendRegisterMessage($user)
    {
        $auth = $this->getSettingService()->get('auth', array());

        if (empty($auth['welcome_enabled'])
            || $auth['welcome_enabled'] != 'opened'
            || empty($auth['welcome_sender'])) {
            return;
        }

        $senderUser = $this->getUserService()->getUserByNickname($auth['welcome_sender']);

        if (empty($senderUser)) {
            return;
        }

        $welcomeBody = $this->getWelcomeBody($user);

        if (empty($welcomeBody)) {
            return;
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
        $site = $this->getSettingService()->get('site', array());
        $valuesToBeReplace = array('{{nickname}}', '{{sitename}}', '{{siteurl}}');
        $valuesToReplace = array($user['nickname'], $site['name'], $site['url']);

        $auth = $this->getSettingService()->get('auth', array());
        $welcomeBody = '';
        if (!empty($auth) && isset($auth['welcome_body'])) {
            $welcomeBody = $auth['welcome_body'];
        }

        $welcomeBody = str_replace($valuesToBeReplace, $valuesToReplace, $welcomeBody);

        return $welcomeBody;
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    protected function getMessageService()
    {
        return $this->getBiz()->service('User:MessageService');
    }

    private function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }
}
