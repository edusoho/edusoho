<?php

namespace Biz\Coupon\Service;

interface CouponBatchResourceService
{
    public function countCouponBatchResource($conditions);

    public function searchCouponBatchResource($conditions, $orderBy, $start, $limit);

    public function findResourcesByBatchId($batchId);
}
