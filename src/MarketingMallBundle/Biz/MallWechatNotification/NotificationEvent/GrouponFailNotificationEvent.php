<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\NotificationEvent;

use AppBundle\Common\SmsToolkit;
use Biz\Sms\SmsType;
use MarketingMallBundle\Common\WechatNotification\MessageSubscriberTemplateUtil;
use MarketingMallBundle\Common\WechatNotification\MessageTemplateUtil;

class GrouponFailNotificationEvent extends AbstractNotificationEvent implements NotificationEvent
{
    public function getServiceFollowTemplateKey()
    {
        return MessageTemplateUtil::TEMPLATE_GROUPON_FAIL;
    }

    public function getMessageSubscribeTemplateKey()
    {
        return MessageSubscriberTemplateUtil::TEMPLATE_GROUPON_FAIL;
    }

    public function getSmsTemplateKey()
    {
        return SmsType::GROUPON_FAIL;
    }

    public function buildServiceFollowTemplateArgs($data)
    {
        return [
            'first' => ['value' => '你的订单未拼团成功，可到商城重新参与拼团活动'],
            'keyword1' => ['value' => $data['grouponTitle']],
            'keyword2' => ['value' => $data['grouponMemberNum'] . '人'],
            'remark' => ['value' => '点击查看活动详情'],
        ];
    }

    public function buildMessageSubscribeTemplateArgs($data)
    {
        return [
            'thing9' => ['value' => $data['grouponTitle']],
            'number5' => ['value' => $data['grouponMemberNum']],
            'thing2' => ['value' => '失败'],
            'thing12' => ['value' => $this->transFailReason($data['grouponFailReason'])],
            'thing3' => ['value' => '点击查看活动详情'],
        ];
    }

    public function buildSmsTemplateArgs($data)
    {
        return [
            'grouponTitle' => "【{$data['grouponMemberNum']}人团】" . $data['grouponTitle'],
            'url' => SmsToolkit::getShortLink($data['url']),
        ];
    }

    public function getToUserIds($data)
    {
        return $data['userIds'];
    }

    public function getOpenIdMap($data)
    {
        return $data['openIds'];
    }

    public function getSubscribeStatus($data)
    {
        return $data['isSubscribed'];
    }

    public function getGotoUrl($data)
    {
        return $data['url'];
    }

    private function transFailReason($failReason)
    {
        $failReasons = [
            'timeOver' => '超出成团时限',
            'grouponBatchFinished' => '拼团活动结束',
            'grouponBatchInvalid' => '拼团活动失效',
            'grouponCancel' => '拼团被取消',
        ];

        return $failReasons[$failReason];
    }
}
