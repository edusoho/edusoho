<?php

namespace MarketingMallBundle\Common\WechatNotification;

use Biz\Sms\SmsType;

class MessageSubscriberTemplateUtil
{
    const TEMPLATE_GROUPON_CREATE = 'grouponCreate';
    const TEMPLATE_GROUPON_CREATE_ID = 3453;

    const TEMPLATE_GROUPON_JOIN = 'grouponJoin';
    const TEMPLATE_GROUPON_JOIN_ID = 3451;

    const TEMPLATE_GROUPON_SUCCESS = 'grouponSuccess';
    const TEMPLATE_GROUPON_SUCCESS_ID = 555;

    const TEMPLATE_GROUPON_FAIL = 'grouponFail';
    const TEMPLATE_GROUPON_FAIL_ID = 1111;

    const TEMPLATE_GROUPON_ORDER_REFUND = 'grouponOrderRefund';
    const TEMPLATE_GROUPON_ORDER_REFUND_ID = 1114;

    public static function templates()
    {
        return [
            self::TEMPLATE_GROUPON_CREATE => [
                'id' => self::TEMPLATE_GROUPON_CREATE_ID,
                'name' => 'wechat.notification.template_name.groupon_create',
                'content' => 'wechat.notification.message_subscribe_template.groupon_create',
                'position' => 'wechat.notification.message_subscribe_template.position.groupon_page',
                'sms_content' => 'wechat.notification.message_subscribe_sms.template.groupon_create',
                'rule' => 'wechat.notification.condition.groupon_create',
                'detail' => '课程名称：{{thing1.DATA}}<br>拼团价：{{amount2.DATA}}<br>成团人数：{{number3.DATA}}<br>剩余时间：{{time4.DATA}}<br>备注：{{thing10.DATA}}',
                'smsDetail' => [SmsType::GROUPON_CREATE => '你参与的{{title.DATA}}已开团成功，拼团价为{{price.DATA}}元，需{{num.DATA}}人成团，拼团结束时间为{{endAt.DATA}}，快复制链接邀请微信好友一起参与吧{{url.DATA}}'],
                'kidList' => ['1', '2', '3', '4', '10'],
                'object' => '购买者',
                'sceneDesc' => '开团成功通知',
                'role' => 'ROLE_USER',
                'status' => 1,
            ],
            self::TEMPLATE_GROUPON_JOIN => [
                'id' => self::TEMPLATE_GROUPON_JOIN_ID,
                'name' => 'wechat.notification.template_name.groupon_join',
                'content' => 'wechat.notification.message_subscribe_template.groupon_join',
                'position' => 'wechat.notification.message_subscribe_template.position.groupon_page',
                'sms_content' => 'wechat.notification.message_subscribe_sms.template.groupon_join',
                'rule' => 'wechat.notification.condition.groupon_join',
                'detail' => '课程名称：{{thing1.DATA}}<br>拼团价：{{amount2.DATA}}<br>参团人数：{{character_string8.DATA}}<br>剩余时间：{{time3.DATA}}<br>温馨提示：{{thing4.DATA}}',
                'smsDetail' => [SmsType::GROUPON_JOIN => '你参与的{{title.DATA}}已参团成功，拼团价为{{price.DATA}}元，还剩{{remain.DATA}}人成团，拼团结束时间为{{endAt.DATA}}，快复制链接邀请微信好友一起参与吧{{url.DATA}}'],
                'kidList' => ['1', '2', '8', '3', '4'],
                'object' => '购买者',
                'sceneDesc' => '参团成功通知',
                'role' => 'ROLE_USER',
                'status' => 1,
            ],
            self::TEMPLATE_GROUPON_SUCCESS => [
                'id' => self::TEMPLATE_GROUPON_SUCCESS_ID,
                'name' => 'wechat.notification.template_name.groupon_success',
                'content' => 'wechat.notification.message_subscribe_template.groupon_success',
                'position' => 'wechat.notification.message_subscribe_template.position.groupon_page',
                'sms_content' => 'wechat.notification.message_subscribe_sms.template.groupon_success',
                'rule' => 'wechat.notification.condition.groupon_success',
                'detail' => '拼团商品：{{thing10.DATA}}<br>拼团价：{{amount11.DATA}}<br>拼团状态：{{phrase2.DATA}}<br>温馨提示：{{thing3.DATA}}',
                'smsDetail' => [SmsType::GROUPON_SUCCESS => '你参与的{{grouponTitle.DATA}}已成团，拼团价为{{grouponPrice.DATA}}元，快复制链接到微信中去学习吧{{url.DATA}}'],
                'kidList' => ['10', '11', '2', '3'],
                'object' => '购买者',
                'sceneDesc' => '拼团成功通知',
                'role' => 'ROLE_USER',
                'status' => 1,
            ],
            self::TEMPLATE_GROUPON_FAIL => [
                'id' => self::TEMPLATE_GROUPON_FAIL_ID,
                'name' => 'wechat.notification.template_name.groupon_fail',
                'content' => 'wechat.notification.message_subscribe_template.groupon_fail',
                'position' => 'wechat.notification.message_subscribe_template.position.groupon_page',
                'sms_content' => 'wechat.notification.message_subscribe_sms.template.groupon_fail',
                'rule' => 'wechat.notification.condition.groupon_fail',
                'detail' => '拼团商品：{{thing9.DATA}}<br>拼团人数：{{number5.DATA}}<br>拼团状态：{{thing2.DATA}}<br>失败原因：{{thing12.DATA}}<br>温馨提示：{{thing3.DATA}}',
                'smsDetail' => [SmsType::GROUPON_FAIL => '你参与的{{grouponTitle.DATA}}拼团失败，可复制链接到微信内重新参与拼团{{url.DATA}}'],
                'kidList' => ['9', '5', '2', '12', '3'],
                'object' => '购买者',
                'sceneDesc' => '拼团失败通知',
                'role' => 'ROLE_USER',
                'status' => 1,
            ],
            self::TEMPLATE_GROUPON_ORDER_REFUND => [
                'id' => self::TEMPLATE_GROUPON_ORDER_REFUND_ID,
                'name' => 'wechat.notification.template_name.groupon_order_refund',
                'content' => 'wechat.notification.message_subscribe_template.groupon_order_refund',
                'position' => 'wechat.notification.message_subscribe_template.position.groupon_page',
                'sms_content' => 'wechat.notification.message_subscribe_sms.template.groupon_order_refund',
                'rule' => 'wechat.notification.condition.groupon_order_refund',
                'detail' => '课程名称：{{thing11.DATA}}<br>支付金额：{{amount3.DATA}}<br>退款金额：{{amount12.DATA}}<br>温馨提示：{{thing5.DATA}}',
                'smsDetail' => [SmsType::GROUPON_ORDER_REFUND => '你参与的{{grouponTitle.DATA}}拼团失败，发起退款，退款金额为{{refundAmount.DATA}}元，复制链接至微信查看退款详情{{url.DATA}}'],
                'kidList' => ['11', '3', '12', '5'],
                'object' => '购买者',
                'sceneDesc' => '退款成功通知',
                'role' => 'ROLE_USER',
                'status' => 1,
            ],
        ];
    }
}
