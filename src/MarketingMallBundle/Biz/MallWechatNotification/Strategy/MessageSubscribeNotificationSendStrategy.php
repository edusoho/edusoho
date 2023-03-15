<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\Strategy;

use MarketingMallBundle\Biz\MallWechatNotification\Event\NotificationEvent;
use MarketingMallBundle\Common\WechatNotification\MessageSubscriberTemplateUtil;

class MessageSubscribeNotificationSendStrategy extends AbstractNotificationSendStrategy implements NotificationSendStrategy
{
    public function send(NotificationEvent $event, array $data)
    {
        $toUserIds = $this->filterLockUser($event->getToUserIds($data));
        if (empty($toUserIds)) {
            return;
        }
        $templateKey = $event->getMessageSubscribeTemplateKey();
        $templateId = $this->getWeChatService()->getSubscribeTemplateId($templateKey);
        if ($templateId) {
            $subscribeRecords = $this->getWeChatService()->findOnceSubscribeRecordsByTemplateCodeUserIds($templateId, $toUserIds);
            $templates = MessageSubscriberTemplateUtil::templates();
            $notification = [
                'channel' => $this->getWeChatService()->getWeChatSendChannel(),
                'template_id' => $templateId,
                'template_code' => $templates[$templateKey]['id'],
                'template_args' => $event->buildMessageSubscribeTemplateArgs($data),
                'goto' => ['type' => 'url', 'url' => $event->getGotoUrl($data)],
            ];
            $notifications = [];
            foreach ($subscribeRecords as $subscribeRecord) {
                $notification['to_id'] = $subscribeRecord['toId'];
                $notifications[] = $notification;
            }
            if ($notifications) {
                $notificationBatch = $this->getWeChatService()->sendSubscribeWeChatNotification($templateKey, 'wechat_subscribe_notify_'.$templateKey, $notifications);
                if ($notificationBatch) {
                    $this->getWeChatService()->updateSubscribeRecordsByIds(array_column($subscribeRecords, 'id'), ['isSend' => 1]);
                }
            }
        }
        $this->getWeChatService()->sendSubscribeSms(
            $templateKey,
            array_diff($toUserIds, array_column($subscribeRecords ?? [], 'userId')),
            $event->getSmsTemplateKey(),
            $event->buildSmsTemplateArgs($data),
            $notificationBatch['id'] ?? 0
        );
    }
}
