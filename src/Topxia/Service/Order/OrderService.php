<?php

namespace Topxia\Service\Order;

interface OrderService
{
	public function searchCouponsCount($conditions);

	public function searchCoupons($conditions, $sort = 'latest', $start, $limit);

	public function deleteCoupon($couponId);

	public function generateCoupon($couponData);
}