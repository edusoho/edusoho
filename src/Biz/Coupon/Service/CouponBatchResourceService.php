<?php

namespace Biz\Coupon\Service;

interface CouponBatchResourceService
{
    public function isCouponTarget($batchId, $targetId);

    public function countCouponBatchResource($conditions);

    public function searchCouponBatchResource($conditions, $orderBy, $start, $limit);
}
