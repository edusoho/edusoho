<?php
namespace Topxia\Service\Coupon;

interface CouponService
{
	public function searchCouponsCount($conditions);

	public function searchCoupons($conditions, $sort = 'latest', $start, $limit);

	public function deleteCoupon($id);

	/*public function generateCoupon($couponData);*/
}