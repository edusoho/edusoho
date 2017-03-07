<?php


namespace Biz\Coupon\Type;


class VipCoupon extends BaseCoupon
{

    /**
     * @inheritDoc
     */
    public function canUseable($coupon, $target)
    {
        return isset($target['id']) && $coupon['targetId'] === $target['id'];
    }
}