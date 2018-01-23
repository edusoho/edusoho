<?php

namespace Biz\Distributor\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserMessageSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'user.change_nickname' => 'onChangeUserMessage',
            'user.change_mobile' => 'onChangeUserMessage',
        );
    }

    public function onChangeUserMessage(Event $event)
    {
        $context = $event->getSubject();
        $user = $this->getUserService()->getUser($context['id']);
        if (!empty($user) && 'distributor' == $user['type']) {
            $user['token'] = $user['distributorToken'];
            $this->getDistributorUserService()->createJobData($user);
        }
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getDistributorUserService()
    {
        return $this->getBiz()->service('Distributor:DistributorUserService');
    }
}
