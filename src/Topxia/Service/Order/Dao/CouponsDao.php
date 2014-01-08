<?php

namespace Topxia\Service\Order\Dao;

interface CouponsDao
{

    public function searchCouponsCount($conditions);

    public function searchCoupons($conditions, $orderBy, $start, $limit);

    public function deleteCoupon($couponId);

    public function generateCoupon($coupon);

    public function getCoupon($id);

}