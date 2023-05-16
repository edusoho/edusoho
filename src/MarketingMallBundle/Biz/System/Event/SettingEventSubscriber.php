<?php

namespace MarketingMallBundle\Biz\System\Event;

use Biz\System\Service\PaymentSettingService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
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
        $setting = $this->getPaymentSettingService()->get();

        $this->getMallClient()->setPaymentSetting([
            'enabled' => (bool) $setting['wxpay_enabled'] ?? false,
        ]);

        $wap = $this->getSettingService()->get('wap');
        if (empty($wap['version'])) {
            $this->getSettingService()->set('wap', [
                'version' => 2,
                'template' => 'sail',
            ]);
        }
    }

    /**
     * @return MarketingMallClient
     */
    public function getMallClient()
    {
        return new MarketingMallClient($this->getBiz());
    }

    /**
     * @return PaymentSettingService
     */
    protected function getPaymentSettingService()
    {
        return $this->getBiz()->service('System:PaymentSettingService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
