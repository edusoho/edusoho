<?php
namespace Topxia\Service\Coupon;

interface CouponService
{
    public function getCoupon($id);

    public function getCouponsByIds($ids);

    public function addCoupon($coupon);

    public function updateCoupon($couponId, $fields);

    public function findCouponsByBatchId($batchId, $start, $limit);

    public function findCouponsByIds(array $ids);

    public function searchCoupons(array $conditions, $orderBy, $start, $limit);

    public function searchCouponsCount(array $conditions);

    public function generateInviteCoupon($userId, $mode);

    public function getCouponByCode($code);

    /**
     * array(
     *   'useable' => 'yes', // no
     *   'message' => '', // no的时候才会有值
     *   'decreaseAmount' => 1.50,
     *   'afterAmount' => 5.00,
     * )
     */
    public function deleteCouponsByBatch($batchId);

    public function checkCouponUseable($code, $targetType, $targetId, $amount);

    public function useCoupon($code, $order);
}
