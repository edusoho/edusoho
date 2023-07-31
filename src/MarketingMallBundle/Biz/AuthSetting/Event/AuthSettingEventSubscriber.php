<?php

namespace MarketingMallBundle\Biz\AuthSetting\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use MarketingMallBundle\Biz\Mall\Service\MallService;
use MarketingMallBundle\Client\MarketingMallClient;

class AuthSettingEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'user.protocol.update' => 'onUserProtocolUpdate',
        ];
    }

    public function onUserProtocolUpdate(Event $event)
    {
        if (!$this->getMallService()->isInit()) {
            return;
        }
        $userProtocol = $event->getSubject();

        $this->getMallClient()->notifyUserProtocolUpdate($userProtocol);
    }

    protected function getMallClient()
    {
        return new MarketingMallClient($this->getBiz());
    }

    /**
     * @return MallService
     */
    protected function getMallService()
    {
        return $this->getBiz()->service('Mall:MallService');
    }
}
