<?php

namespace Biz\Coupon\State;

interface CouponInterface
{
    public function using();

    public function used($params);

    public function cancelUsing();
}
