<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\NotificationEvent;

use AppBundle\Common\SmsToolkit;
use Biz\Sms\SmsType;
use MarketingMallBundle\Common\WechatNotification\MessageSubscriberTemplateUtil;
use MarketingMallBundle\Common\WechatNotification\MessageTemplateUtil;

class GrouponOrderRefundNotificationEvent extends AbstractNotificationEvent implements NotificationEvent
{
    public function getServiceFollowTemplateKey()
    {
        return MessageTemplateUtil::TEMPLATE_GROUPON_ORDER_REFUND;
    }

    public function getMessageSubscribeTemplateKey()
    {
        return MessageSubscriberTemplateUtil::TEMPLATE_GROUPON_ORDER_REFUND;
    }

    public function getSmsTemplateKey()
    {
        return SmsType::GROUPON_ORDER_REFUND;
    }

    public function buildServiceFollowTemplateArgs($data)
    {
        return [
            'first' => ['value' => "很遗憾，你的{$data['grouponMemberNum']}人拼团未成功，已支付费用将原支付渠道退还。"],
            'keyword1' => ['value' => '拼团失败，系统自动退款'],
            'keyword2' => ['value' => ($data['refundAmount'] / 100) . '元'],
            'remark' => ['value' => '点击查看退款详情'],
        ];
    }

    public function buildMessageSubscribeTemplateArgs($data)
    {
        return [
            'thing11' => ['value' => $data['grouponTitle']],
            'amount3' => ['value' => ($data['payAmount'] / 100) . '元'],
            'amount12' => ['value' => ($data['refundAmount'] / 100) . '元'],
            'thing5' => ['value' => '拼团失败，费用将在3天内原渠道退还。'],
        ];
    }

    public function buildSmsTemplateArgs($data)
    {
        return [
            'grouponTitle' => "【{$data['grouponMemberNum']}人团】" . $data['grouponTitle'],
            'refundAmount' => $data['refundAmount'] / 100,
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
}
