<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\SettingService;
use Biz\WeChat\Service\WeChatService;
use Biz\WeChat\WechatNotificationType;
use MarketingMallBundle\Biz\Mall\Service\MallService;
use MarketingMallBundle\Biz\MallWechatNotification\NotificationEvent\NotificationEventFactory;
use MarketingMallBundle\Biz\MallWechatNotification\Service\MallWechatNotificationService;
use MarketingMallBundle\Biz\MallWechatNotification\Strategy\MessageSubscribeNotificationSendStrategy;
use MarketingMallBundle\Biz\MallWechatNotification\Strategy\NotificationSendStrategy;
use MarketingMallBundle\Biz\MallWechatNotification\Strategy\ServiceFollowNotificationSendStrategy;
use MarketingMallBundle\Common\WechatNotification\MessageSubscriberTemplateUtil;
use MarketingMallBundle\Common\WechatNotification\MessageTemplateUtil;

class MallWechatNotificationServiceImpl extends BaseService implements MallWechatNotificationService
{
    public function notify($eventName, $data)
    {
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        $wechatNotificationSetting = $this->getSettingService()->get('wechat_notification', []);
        if (!$this->isWechatNotificationEnabled($wechatSetting, $wechatNotificationSetting)) {
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
        if (!$this->getMallService()->isInit()) {
            return;
        }
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        $wechatNotificationSetting = $this->getSettingService()->get('wechat_notification', []);
        if (!$this->isWechatNotificationEnabled($wechatSetting, $wechatNotificationSetting)) {
            return;
        }
        if ($this->isInit($wechatSetting, $wechatNotificationSetting)) {
            return;
        }
        if (WechatNotificationType::MESSAGE_SUBSCRIBE == $wechatNotificationSetting['notification_type']) {
            $this->initMessageSubscribeTemplates();
        } else {
            $this->initServiceFollowTemplates();
        }
    }

    private function isWechatNotificationEnabled($wechatSetting, $wechatNotificationSetting)
    {
        if (empty($wechatSetting['wechat_notification_enabled'])) {
            return false;
        }
        if (WechatNotificationType::MESSAGE_SUBSCRIBE == $wechatNotificationSetting['notification_type'] && empty($wechatNotificationSetting['is_authorization'])) {
            return false;
        }

        return true;
    }

    private function isInit($wechatSetting, $wechatNotificationSetting)
    {
        $templates = MessageTemplateUtil::templates();
        foreach (array_keys($templates) as $key) {
            if (!empty($wechatSetting['templates'][$key])) {
                return true;
            }
        }
        $templates = MessageSubscriberTemplateUtil::templates();
        foreach (array_keys($templates) as $key) {
            if (!empty($wechatNotificationSetting['templates'][$key])) {
                return true;
            }
        }

        return false;
    }

    private function initServiceFollowTemplates()
    {
        $templates = MessageTemplateUtil::templates();
        foreach (array_keys($templates) as $key) {
            $this->getWeChatService()->addTemplate($templates[$key], $key, WechatNotificationType::SERVICE_FOLLOW);
            $this->getWeChatService()->saveWeChatTemplateSetting($key, ['status' => 1], WechatNotificationType::SERVICE_FOLLOW);
        }
    }

    private function initMessageSubscribeTemplates()
    {
        $templates = MessageSubscriberTemplateUtil::templates();
        foreach (array_keys($templates) as $key) {
            $this->getWeChatService()->addTemplate($templates[$key], $key, WechatNotificationType::MESSAGE_SUBSCRIBE);
            $this->getWeChatService()->saveWeChatTemplateSetting($key, ['status' => 1], WechatNotificationType::MESSAGE_SUBSCRIBE);
        }
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
     * @return MallService
     */
    protected function getMallService()
    {
        return $this->createService('Mall:MallService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->createService('WeChat:WeChatService');
    }
}
