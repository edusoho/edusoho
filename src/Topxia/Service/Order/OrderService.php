<?php

namespace Topxia\Service\Order;

interface OrderService
{
	public function searchCourseCouponsCount($conditions);

	public function searchCourseCoupons($conditions, $sort = 'latest', $start, $limit);

	public function deleteCoupon($couponId);

	public function generateCoupon($couponData);
}