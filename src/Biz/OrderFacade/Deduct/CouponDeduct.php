<?php

namespace Biz\OrderFacade\Deduct;

use Biz\Coupon\Service\CouponService;
use Codeages\Biz\Framework\Order\Status\OrderStatusCallback;

class CouponDeduct extends Deduct implements OrderStatusCallback
{
    const TYPE = 'coupon';

    public function onCreated($orderDeduct)
    {
        $coupon = $this->getCouponService()->getCouponStateById($orderDeduct['deduct_id']);

        $params = array(
            'userId' => $orderDeduct['user_id'],
            'orderId' => $orderDeduct['order_id'],
            'targetType' => '',
            'targetId' => 0,
        );

        if ($orderDeduct['item']) {
            $params['targetType'] = $orderDeduct['item']['target_type'];
            $params['targetId'] = $orderDeduct['item']['target_id'];
        }
        $coupon->using($params);
    }

    public function onClosed($orderDeduct)
    {
        $coupon = $this->getCouponService()->getCouponStateById($orderDeduct['deduct_id']);
        $coupon->cancelUsing();
    }

    public function onPaid($orderDeduct)
    {
        $coupon = $this->getCouponService()->getCouponStateById($orderDeduct['deduct_id']);
        $coupon->used();

        return OrderStatusCallback::SUCCESS;
    }

    /**
     * @return CouponService
     */
    protected function getCouponService()
    {
        return $this->biz->service('Coupon:CouponService');
    }
}
