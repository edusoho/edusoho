<?php

namespace Topxia\Service\Coupon\Dao;

interface CouponDao
{

    public function searchCouponsCount($conditions);

    public function searchCoupons($conditions, $orderBy, $start, $limit);

    public function deleteCoupon($id);

/*    public function generateCoupon($coupon);

    public function getCoupon($id);*/

}