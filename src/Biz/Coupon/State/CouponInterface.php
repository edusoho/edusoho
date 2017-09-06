<?php

namespace Biz\Coupon\State;

interface CouponInterface
{
    public function using($params);

    public function used();

    public function cancelUsing();
}
