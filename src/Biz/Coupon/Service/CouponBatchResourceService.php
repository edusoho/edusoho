<?php

namespace Biz\Coupon\Service;

interface CouponBatchResourceService
{
    public function isCouponTarget($batchId, $targetId);
}
