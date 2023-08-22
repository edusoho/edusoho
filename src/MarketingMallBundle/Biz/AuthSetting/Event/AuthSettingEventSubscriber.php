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
            'user.auth.setting.update' => 'onUserAuthSettingUpdate',
        ];
    }

    public function onUserAuthSettingUpdate(Event $event)
    {
        if (!$this->getMallService()->isInit()) {
            return;
        }
        $auth = $event->getSubject();
        $this->getMallClient()->notifyUserProtocolUpdate([
            'userTerms' => $auth['user_terms'] ?? 'closed',
            'userTermsBody' => $auth['user_terms_body'] ?? '',
            'privacyPolicy' => $auth['privacy_policy'] ?? 'closed',
            'privacyPolicyBody' => $auth['privacy_policy_body'] ?? '',
        ]);

        $this->getMallClient()->notifyPasswordLevelUpdate([
            'passwordLevel' => $auth['password_level'] ?? 'low',
        ]);
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
        return $this->getBiz()->service('MarketingMallBundle:Mall:MallService');
    }
}
