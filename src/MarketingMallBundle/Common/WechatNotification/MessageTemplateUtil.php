<?php

namespace MarketingMallBundle\Common\WechatNotification;

class MessageTemplateUtil
{
    const TEMPLATE_GROUPON_CREATE = 'grouponCreate';
    const TEMPLATE_GROUPON_CREATE_ID = 'OPENTM416609408';

    const TEMPLATE_GROUPON_JOIN = 'grouponJoin';
    const TEMPLATE_GROUPON_JOIN_ID = 'OPENTM417363301';

    const TEMPLATE_GROUPON_SUCCESS = 'grouponSuccess';
    const TEMPLATE_GROUPON_SUCCESS_ID = 'OPENTM417855307';

    const TEMPLATE_GROUPON_FAIL = 'grouponFail';
    const TEMPLATE_GROUPON_FAIL_ID = 'OPENTM415259092';

    const TEMPLATE_GROUPON_ORDER_REFUND = 'grouponOrderRefund';
    const TEMPLATE_GROUPON_ORDER_REFUND_ID = 'OPENTM414474089';

    public static function templates()
    {
        return [
            self::TEMPLATE_GROUPON_CREATE => [
                'id' => self::TEMPLATE_GROUPON_CREATE_ID,
                'name' => 'wechat.notification.template_name.groupon_create',
                'content' => 'wechat.notification.template.groupon_create',
                'rule' => 'wechat.notification.condition.groupon_create',
                'detail' => '{{first.DATA}}<br>课程名称：{{keyword1.DATA}}<br>拼团价：{{keyword2.DATA}}<br>成团人数：{{keyword3.DATA}}<br>拼团结束时间：{{keyword4.DATA}}<br>{{remark.DATA}}',
                'object' => '购买者',
                'status' => 1,
            ],
            self::TEMPLATE_GROUPON_JOIN => [
                'id' => self::TEMPLATE_GROUPON_JOIN_ID,
                'name' => 'wechat.notification.template_name.groupon_join',
                'content' => 'wechat.notification.template.groupon_join',
                'rule' => 'wechat.notification.condition.groupon_join',
                'detail' => '{{first.DATA}}<br>课程名称：{{keyword1.DATA}}<br>拼团价：{{keyword2.DATA}}<br>成团人数：{{keyword3.DATA}}<br>拼团结束时间：{{keyword4.DATA}}<br>{{remark.DATA}}',
                'object' => '购买者',
                'status' => 1,
            ],
            self::TEMPLATE_GROUPON_SUCCESS => [
                'id' => self::TEMPLATE_GROUPON_SUCCESS_ID,
                'name' => 'wechat.notification.template_name.groupon_success',
                'content' => 'wechat.notification.template.groupon_success',
                'rule' => 'wechat.notification.condition.groupon_success',
                'detail' => '{{first.DATA}}<br>商品名称：{{keyword1.DATA}}<br>拼团价：{{keyword2.DATA}}<br>拼团状态：{{keyword3.DATA}}<br>{{remark.DATA}}',
                'object' => '购买者',
                'status' => 1,
            ],
            self::TEMPLATE_GROUPON_FAIL => [
                'id' => self::TEMPLATE_GROUPON_FAIL_ID,
                'name' => 'wechat.notification.template_name.groupon_fail',
                'content' => 'wechat.notification.template.groupon_fail',
                'rule' => 'wechat.notification.condition.groupon_fail',
                'detail' => '{{first.DATA}}<br>拼团商品：{{keyword1.DATA}}<br>拼团人数：{{keyword2.DATA}}<br>{{remark.DATA}}',
                'object' => '购买者',
                'status' => 1,
            ],
            self::TEMPLATE_GROUPON_ORDER_REFUND => [
                'id' => self::TEMPLATE_GROUPON_ORDER_REFUND_ID,
                'name' => 'wechat.notification.template_name.groupon_order_refund',
                'content' => 'wechat.notification.template.groupon_order_refund',
                'rule' => 'wechat.notification.condition.groupon_order_refund',
                'detail' => '{{first.DATA}}<br>退款原因：{{keyword1.DATA}}<br>退款金额：{{keyword2.DATA}}<br>{{remark.DATA}}',
                'object' => '购买者',
                'status' => 1,
            ]
        ];
    }
}
