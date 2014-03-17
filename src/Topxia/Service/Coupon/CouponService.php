<?php
namespace Topxia\Service\Coupon;

interface CouponService
{	
	public function getBatch ($id);

	public function searchCoupons (array $conditions, $sort = 'latest', $start, $limit);

	public function searchCouponsCount(array $conditions);
	
	public function generateCoupon($couponData);

	public function searchBatchs (array $conditions, $sort = 'latest', $start, $limit);

	public function searchBatchsCount(array $conditions);

	public function deleteBatch($id);

	public function checkPrefix($prefix);
}