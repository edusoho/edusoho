<?php

namespace Biz\Coupon\Type;

class VipCoupon extends BaseCoupon
{
    public function canUseable($coupon, $target)
    {
        return isset($target['id']) && $coupon['targetId'] === $target['id'];
    }
}
