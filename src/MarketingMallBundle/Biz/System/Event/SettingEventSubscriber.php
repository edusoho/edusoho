<?php

namespace MarketingMallBundle\Biz\System\Event;

use Biz\System\Service\LoginBindSettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use MarketingMallBundle\Client\MarketingMallClient;

class SettingEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'marketing_mall.init' => 'onMarketingMallInit',
        ];
    }

    public function onMarketingMallInit(Event $event)
    {
        $loginConnect = $this->getLoginBindSettingService()->get();

        $this->syncWechatMobileSetting([
            'appId' => $loginConnect['weixinmob_key'] ?? '',
            'appSecret' => $loginConnect['weixinmob_secret'] ?? '',
            'mpFileCode' => $loginConnect['weixinmob_mp_secret'] ?? '',
        ]);
    }

    protected function syncWechatMobileSetting($setting)
    {
        $client = new MarketingMallClient($this->getBiz());
        $result = $client->setWechatMobileSetting($setting);

        if (empty($result['ok'])) {
            throw new ServiceException('设置微信移动端登录失败，请重试！(' . ($result['message'] ?? '') . ')');
        }
    }

    /**
     * @return LoginBindSettingService
     */
    protected function getLoginBindSettingService()
    {
        return $this->getBiz()->service('System:LoginBindSettingService');
    }
}
