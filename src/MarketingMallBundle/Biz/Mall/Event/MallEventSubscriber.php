<?php

namespace MarketingMallBundle\Biz\Mall\Event;

use Biz\System\Service\LoginBindSettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use MarketingMallBundle\Client\MarketingMallClient;

class MallEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'setting.school.logo.update' => 'notifySchoolLogo',
            'setting.login_bind.set' => 'onLoginBindSettingSet',
        ];
    }

    public function notifySchoolLogo(Event $event)
    {
        $client = new MarketingMallClient($this->getBiz());
        $client->notifyUpdateLogo();
    }

    public function onLoginBindSettingSet(Event $event)
    {
        $loginConnect = $this->getLoginBindSettingService()->get();
        //todo 判断是否初始化商城
        $this->syncWechatMobileSetting([
            'appId' => $loginConnect['weixinmob_key'] ?? '',
            'appSecret' => $loginConnect['weixinmob_secret'] ?? '',
            'mpFileCode' => $loginConnect['weixinmob_mp_secret'] ?? '',
        ]);
    }

    protected function syncWechatMobileSetting($setting)
    {
        $client = new MarketingMallClient($this->getBiz());
        $client->setWechatMobileSetting($setting);
    }

    /**
     * @return LoginBindSettingService
     */
    protected function getLoginBindSettingService()
    {
        return $this->getBiz()->service('System:LoginBindSettingService');
    }
}
