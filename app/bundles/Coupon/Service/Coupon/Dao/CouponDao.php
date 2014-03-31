<?php

namespace Coupon\Service\Coupon\Dao;

interface CouponDao
{
	public function findCouponsByBatchId($batchId, $start, $limit);

	public function searchCoupons($conditions, $orderBy, $start, $limit);
	
    public function searchCouponsCount(array $conditions);

    public function deleteCouponsByBatch($id);

    public function addCoupon($coupons);

}