<?php

namespace Topxia\Service\Coupon\Dao;

interface CouponDao
{
    public function getCoupon($id);

    public function getCouponsByIds($ids);

    public function getCouponByCode($code, $lock = false);

    public function updateCoupon($id, $fields);

    public function findCouponsByBatchId($batchId, $start, $limit);

    public function findCouponsByIds(array $ids);

    public function searchCoupons($conditions, $orderBy, $start, $limit);

    public function searchCouponsCount(array $conditions);

    public function deleteCouponsByBatch($id);

    public function addCoupon($coupons);

}
