<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\Strategy;

use Biz\AppLoggerConstant;
use Biz\Notification\Service\NotificationService;
use Biz\System\Service\LogService;
use MarketingMallBundle\Biz\MallWechatNotification\NotificationEvent\NotificationEvent;
use MarketingMallBundle\Common\WechatNotification\MessageTemplateUtil;

class ServiceFollowNotificationSendStrategy extends AbstractNotificationSendStrategy implements NotificationSendStrategy
{
    public function send(NotificationEvent $event, array $data)
    {
        $toIds = $this->getToIds($event->getToUserIds($data), $event->getOpenIdMap($data));
        if (empty($toIds)) {
            return;
        }
        $templates = MessageTemplateUtil::templates();
        $templateKey = $event->getServiceFollowTemplateKey();
        $notification = [
            'channel' => $this->getWeChatService()->getWeChatSendChannel(),
            'template_id' => $this->getWeChatService()->getTemplateId($templateKey),
            'template_code' => $templates[$templateKey]['id'],
            'template_args' => $event->buildServiceFollowTemplateArgs($data),
            'goto' => ['type' => 'url', 'url' => $event->getGotoUrl($data)],
        ];
        $notifications = [];
        foreach ($toIds as $toId) {
            $notification['to_id'] = $toId;
            $notifications[] = $notification;
        }
        $this->sendCloudWeChatNotification($templateKey, $notifications);
    }

    private function getToIds($toUserIds, $openIdMap)
    {
        $toUserIds = $this->filterLockUser($toUserIds);
        if (empty($toUserIds)) {
            return [];
        }
        $openIds = [];
        foreach ($toUserIds as $toUserId) {
            if (!empty($openIdMap[$toUserId])) {
                $openIds[] = $openIdMap[$toUserId];
            }
        }

        return $openIds;
    }

    protected function sendCloudWeChatNotification($key, $list)
    {
        $logName = 'wechat_notify_'.$key;
        try {
            $result = $this->getCloudNotificationClient()->sendNotifications($list);
        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::NOTIFY, $logName, "发送微信通知失败:template:{$key}", ['error' => $e->getMessage()]);

            return;
        }

        if (empty($result['sn'])) {
            $this->getLogService()->error(AppLoggerConstant::NOTIFY, $logName, "发送微信通知失败:template:{$key}", $result);

            return;
        }

        $this->getNotificationService()->createWeChatNotificationRecord($result['sn'], $key, $list[0]['template_args'], 'wechat_template');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->biz->service('Notification:NotificationService');
    }

    private function getCloudNotificationClient()
    {
        return $this->biz['ESCloudSdk.notification'];
    }
}
