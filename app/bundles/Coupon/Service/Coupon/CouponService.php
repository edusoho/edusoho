<?php
namespace Coupon\Service\Coupon;

interface CouponService
{	
	public function getBatch ($id);

	public function findBatchsByIds(array $ids);

	public function findCouponsByBatchId($batchId, $start, $limit);

	public function searchCoupons (array $conditions, $orderBy, $start, $limit);

	public function searchCouponsCount(array $conditions);
	
	public function generateCoupon($couponData);

	public function searchBatchs (array $conditions, $orderBy, $start, $limit);

	public function searchBatchsCount(array $conditions);

	public function deleteBatch($id);

	public function checkBatchPrefix($prefix);

	// public function getCouponByCode($code);

	/**
	 * array(
	 *   'useable' => 'yes', // no
	 *   'message' => '', // no的时候才会有值
	 *   'decreaseAmount' => 1.50,
	 *   'afterAmount' => 5.00,
	 * )
	 */
	// public function checkCouponUseable($code, $targetType, $targetId, $amount);

}