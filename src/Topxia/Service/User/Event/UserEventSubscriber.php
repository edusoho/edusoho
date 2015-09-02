<?php
namespace Topxia\Service\User\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class UserEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'user.service.registered' => 'sendRegisterMessage',
        );
    }

    public function  sendRegisterMessage(ServiceEvent $event)
    {
        $user = $event->getSubject();
        $senderUser = array();
        $auth = $this->getSettingService()->get('auth', array());

        if (empty($auth['welcome_enabled'])) {
            return ;
        }

        if ($auth['welcome_enabled'] != 'opened') {
            return ;
        }

        if (empty($auth['welcome_sender'])) {
            return ;
        }
        
        $senderUser = $this->getUserService()->getUserByNickname($auth['welcome_sender']);
        if (empty($senderUser)) {
            return ;
        }

        $welcomeBody = $this->getWelcomeBody($user);
        if (empty($welcomeBody)) {
            return true;
        }

        if (strlen($welcomeBody) >= 1000) {
            $welcomeBody = $this->getWebExtension()->plainTextFilter($welcomeBody, 1000);
        }

        $this->getMessageService()->sendMessage($senderUser['id'], $user['id'], $welcomeBody);
        $conversation = $this->getMessageService()->getConversationByFromIdAndToId($user['id'], $senderUser['id']);
        $this->getMessageService()->deleteConversation($conversation['id']);

    }

    protected function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

    protected function getWelcomeBody($user)
    {
        $site = $this->getSettingService()->get('site', array());
        $valuesToBeReplace = array('{{nickname}}', '{{sitename}}', '{{siteurl}}');
        $valuesToReplace = array($user['nickname'], $site['name'], $site['url']);
        $welcomeBody = $this->getSettingService()->get('auth.welcome_body', '注册欢迎的内容');
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

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

}
