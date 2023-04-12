<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use MarketingMallBundle\Biz\MallWechatNotification\Service\MallWechatNotificationService;

class WechatNotificationEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'marketing_mall.init' => 'onMarketingMallInit',
            'setting.wechat.set' => 'onWechatSettingUpdate',
        ];
    }

    public function onMarketingMallInit(Event $event)
    {
        $this->getMallWechatNotificationService()->init();
    }

    public function onWechatSettingUpdate(Event $event)
    {
        $setting = $event->getSubject();
        if (!empty($setting['wechat_notification_enabled'])) {
            $this->getMallWechatNotificationService()->init();
        }
    }

    /**
     * @return MallWechatNotificationService
     */
    private function getMallWechatNotificationService()
    {
        return $this->getBiz()->service('MarketingMallBundle:MallWechatNotification:MallWechatNotificationService');
    }
}
