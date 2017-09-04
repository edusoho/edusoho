<?php

namespace Biz\Coupon\State;

class ReceiveCoupon extends Coupon implements CouponInterface
{
    public function using($params)
    {
       $this->getCouponService()->updateCoupon(
            $this->coupon['id'],
            array(
                'status' => 'using',
                'targetType' => $params['targetType'],
                'targetId' => $params['targetId'],
                'orderTime' => time(),
                'userId' => $params['userId'],
                'orderId' => $params['orderId'],
            )
        );
    }

    public function used()
    {
        throw new \Exception('Can not directly used coupon which status is unused!');
    }

    public function cancelUsing()
    {
        throw new \Exception('Can not cancel using coupon which status is unused!');
    }
}