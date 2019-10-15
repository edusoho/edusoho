<?php

namespace Biz\Coupon\Type;

class ClassroomCoupon extends BaseCoupon
{
    /**
     * {@inheritdoc}
     */
    public function canUseable($coupon, $target)
    {
        return isset($target['id']) && in_array($target['id'], $coupon['targetIds']);
    }
}
