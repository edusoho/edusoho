<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\SettingService;
use Biz\WeChat\WechatNotificationType;
use MarketingMallBundle\Biz\MallWechatNotification\NotificationEvent\NotificationEventFactory;
use MarketingMallBundle\Biz\MallWechatNotification\Service\MallWechatNotificationService;
use MarketingMallBundle\Biz\MallWechatNotification\Strategy\MessageSubscribeNotificationSendStrategy;
use MarketingMallBundle\Biz\MallWechatNotification\Strategy\NotificationSendStrategy;
use MarketingMallBundle\Biz\MallWechatNotification\Strategy\ServiceFollowNotificationSendStrategy;

class MallWechatNotificationServiceImpl extends BaseService implements MallWechatNotificationService
{
    public function notify($eventName, $data)
    {
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        if (empty($wechatSetting['wechat_notification_enabled'])) {
            return;
        }
        $wechatNotificationSetting = $this->getSettingService()->get('wechat_notification', []);
        if (WechatNotificationType::MESSAGE_SUBSCRIBE == $wechatNotificationSetting['notification_type'] && empty($wechatNotificationSetting['is_authorization'])) {
            return;
        }
        $event = NotificationEventFactory::create($eventName);
        $notificationSendStrategy = $this->getNotificationSendStrategy($event->getServiceFollowTemplateKey(), $event->getMessageSubscribeTemplateKey());
        if ($notificationSendStrategy) {
            $notificationSendStrategy->send($event, $data);
        }
    }

    public function init()
    {
        // TODO: Implement init() method.
    }

    /**
     * @return NotificationSendStrategy
     */
    private function getNotificationSendStrategy($serviceFollowTemplateKey, $messageSubscribeTemplateKey)
    {
        $wechatNotificationSetting = $this->getSettingService()->get('wechat_notification', []);
        if (WechatNotificationType::MESSAGE_SUBSCRIBE == $wechatNotificationSetting['notification_type']) {
            $template = $wechatNotificationSetting['templates'][$messageSubscribeTemplateKey] ?? [];

            return empty($template['status']) || (empty($template['templateId']) && empty($wechatNotificationSetting['notification_sms'])) ? null : new MessageSubscribeNotificationSendStrategy($this->biz);
        }
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        $template = $wechatSetting['templates'][$serviceFollowTemplateKey] ?? [];

        return empty($template['status']) || empty($template['templateId']) ? null : new ServiceFollowNotificationSendStrategy($this->biz);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
