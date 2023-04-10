<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\NotificationEvent;

use AppBundle\Common\SmsToolkit;
use Biz\Sms\SmsType;
use MarketingMallBundle\Common\WechatNotification\MessageSubscriberTemplateUtil;
use MarketingMallBundle\Common\WechatNotification\MessageTemplateUtil;

class GrouponCreateNotificationEvent extends AbstractNotificationEvent implements NotificationEvent
{
    public function getServiceFollowTemplateKey()
    {
        return MessageTemplateUtil::TEMPLATE_GROUPON_CREATE;
    }

    public function getMessageSubscribeTemplateKey()
    {
        return MessageSubscriberTemplateUtil::TEMPLATE_GROUPON_CREATE;
    }

    public function getSmsTemplateKey()
    {
        return SmsType::GROUPON_CREATE;
    }

    public function buildServiceFollowTemplateArgs($data)
    {
        return [
            'first' => ['value' => '你已开团成功，赶紧邀请好友来参团吧！'],
            'keyword1' => ['value' => $data['grouponTitle']],
            'keyword2' => ['value' => ($data['grouponPrice'] / 100) . '元'],
            'keyword3' => ['value' => $data['grouponMemberNum'] . '人'],
            'keyword4' => ['value' => date('Y年m月d日 H:i:s', $data['grouponEndAt'])],
            'remark' => ['value' => '截止至拼团结束时间，拉满成团人数拼团成功，人数不足拼团失败自动退款。点击详情可邀请好友参团。'],
        ];
    }

    public function buildMessageSubscribeTemplateArgs($data)
    {
        return [
            'thing1' => ['value' => $data['grouponTitle']],
            'amount2' => ['value' => ($data['grouponPrice'] / 100) . '元'],
            'number3' => ['value' => $data['grouponMemberNum']],
            'time4' => ['value' => date('Y年m月d日 H:i:s', $data['grouponEndAt'])],
            'thing10' => ['value' => '拉满人数则拼团成功，拼团失败自动退款。'],
        ];
    }

    public function buildSmsTemplateArgs($data)
    {
        return [
            'title' => "【{$data['grouponMemberNum']}人团】".$data['grouponTitle'],
            'price' => $data['grouponPrice'] / 100,
            'num' => $data['grouponMemberNum'],
            'endAt' => date('Y年m月d日 H:i:s', $data['grouponEndAt']),
            'url' => SmsToolkit::getShortLink($data['url']),
        ];
    }

    public function getToUserIds($data)
    {
        return [$data['userId']];
    }

    public function getOpenIdMap($data)
    {
        return [$data['userId'] => $data['openId']];
    }

    public function getSubscribeStatus($data)
    {
        return [$data['userId'] => $data['isSubscribed']];
    }

    public function getGotoUrl($data)
    {
        return $data['url'];
    }
}
