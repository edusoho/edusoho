<?php

namespace Biz\Coupon\State;

class ReceiveCoupon extends Coupon implements CouponInterface
{
    public function using()
    {
        $this->getCouponService()->updateCoupon(
            $this->coupon['id'],
            array(
                'status' => 'using',
            )
        );
    }

    public function used($params)
    {
        throw new \Exception('Can not directly used coupon which status is unused!');
    }

    public function cancelUsing()
    {
        throw new \Exception('Can not cancel using coupon which status is unused!');
    }
}
