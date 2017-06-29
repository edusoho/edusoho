<?php

namespace Biz\Coupon\Type;

class ClassroomCoupon extends BaseCoupon
{
    /**
     * {@inheritdoc}
     */
    public function canUseable($coupon, $target)
    {
        return isset($target['id']) && $coupon['targetId'] === $target['id'];
    }
}
