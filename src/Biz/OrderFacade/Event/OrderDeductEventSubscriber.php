<?php

namespace Biz\OrderFacade\Event;

use Biz\Coupon\Service\CouponService;
use Biz\System\Service\SettingService;
use Biz\User\Service\InviteRecordService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;

class OrderDeductEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'order.deduct.coupon.created' => 'onCouponDeductCreated',
            'order.deduct.coupon.closed' => 'onCouponDeductClosed',
            'order.deduct.coupon.paid' => 'onCouponDeductPaid',
        );
    }

    public function onCouponDeductCreated(Event $event)
    {
        $deduct = $event->getSubject();
        $coupon = $this->getCouponService()->getCouponStateById($deduct['deduct_id']);

        $params = array(
            'userId' => $deduct['user_id'],
            'orderId' => $deduct['order_id'],
            'targetType' => '',
            'targetId' => 0,
        );

        if ($deduct['item']) {
            $params['targetType'] = $deduct['item']['target_type'];
            $params['targetId'] = $deduct['item']['target_id'];
        }
        $coupon->using($params);
    }

    public function onCouponDeductClosed(Event $event)
    {
        $deduct = $event->getSubject();
        $coupon = $this->getCouponService()->getCouponStateById($deduct['deduct_id']);
        $coupon->cancelUsing();
    }

    public function onCouponDeductPaid(Event $event)
    {
        $deduct = $event->getSubject();
        $coupon = $this->getCouponService()->getCouponStateById($deduct['deduct_id']);
        $coupon->used();
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return CouponService
     */
    protected function getCouponService()
    {
        return $this->getBiz()->service('Coupon:CouponService');
    }

    /**
     * @return InviteRecordService
     */
    protected function getInviteRecordService()
    {
        return $this->getBiz()->service('User:InviteRecordService');
    }
}
