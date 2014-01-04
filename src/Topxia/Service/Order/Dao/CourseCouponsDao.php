<?php

namespace Topxia\Service\Order\Dao;

interface CourseCouponsDao
{

    public function searchCourseCouponsCount($conditions);

    public function searchCourseCoupons($conditions, $orderBy, $start, $limit);

    public function deleteCoupon($couponId);

    public function generateCoupon($coupon);

    public function getCoupon($id);

}