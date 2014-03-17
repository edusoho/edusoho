<?php

namespace Topxia\Service\Coupon\Dao;

interface CouponDao
{

	public function searchCoupons($conditions, $orderBy, $start, $limit);
	
    public function searchCouponsCount(array $conditions);

    public function deleteCouponsByBatch($id);

    public function addCoupons($coupons);

}