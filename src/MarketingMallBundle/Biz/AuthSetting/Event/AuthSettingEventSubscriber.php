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
            'user.Auth.setting.update' => 'onUserProtocolUpdate',
        ];
    }

    public function onUserProtocolUpdate(Event $event)
    {
        if (!$this->getMallService()->isInit()) {
            return;
        }
        $auth = $event->getSubject();
        $params = [
            'userTerms' => $auth['user_terms'],
            'userTermsBody' => $auth['user_terms_body'],
            'privacyPolicy' => $auth['privacy_policy'],
            'privacyPolicyBody' => $auth['privacy_policy_body'],
        ];

        $this->getMallClient()->notifyUserProtocolUpdate($params);
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
