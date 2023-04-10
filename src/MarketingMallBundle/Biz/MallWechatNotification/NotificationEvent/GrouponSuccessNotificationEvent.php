<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\NotificationEvent;

use AppBundle\Common\SmsToolkit;
use Biz\Sms\SmsType;
use MarketingMallBundle\Common\WechatNotification\MessageSubscriberTemplateUtil;
use MarketingMallBundle\Common\WechatNotification\MessageTemplateUtil;

class GrouponSuccessNotificationEvent extends AbstractNotificationEvent implements NotificationEvent
{
    public function getServiceFollowTemplateKey()
    {
        return MessageTemplateUtil::TEMPLATE_GROUPON_SUCCESS;
    }

    public function getMessageSubscribeTemplateKey()
    {
        return MessageSubscriberTemplateUtil::TEMPLATE_GROUPON_SUCCESS;
    }

    public function getSmsTemplateKey()
    {
        return SmsType::GROUPON_SUCCESS;
    }

    public function buildServiceFollowTemplateArgs($data)
    {
        return [
            'first' => ['value' => "恭喜你，你已成功拼团{$data['grouponGoodsTitle']}的课程"],
            'keyword1' => ['value' => $data['grouponTitle']],
            'keyword2' => ['value' => ($data['grouponPrice'] / 100) . '元'],
            'keyword3' => ['value' => "拼团成功，{$data['grouponMemberNum']} 人已成团"],
            'remark' => ['value' => '点击详情查看商品'],
        ];
    }

    public function buildMessageSubscribeTemplateArgs($data)
    {
        return [
            'thing10' => ['value' => $data['grouponTitle']],
            'amount11' => ['value' => ($data['grouponPrice'] / 100) . '元'],
            'phrase2' => ['value' => '拼团成功'],
            'thing3' => ['value' => '点击详情查看商品'],
        ];
    }

    public function buildSmsTemplateArgs($data)
    {
        return [
            'grouponTitle' => "【{$data['grouponMemberNum']}人团】" . $data['grouponTitle'],
            'grouponPrice' => $data['grouponPrice'] / 100,
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
