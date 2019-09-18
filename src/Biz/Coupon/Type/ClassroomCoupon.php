<?php

namespace Biz\Coupon\Type;

use Biz\Coupon\Service\CouponBatchResourceService;

class ClassroomCoupon extends BaseCoupon
{
    /**
     * {@inheritdoc}
     */
    public function canUseable($coupon, $target)
    {
        return isset($target['id']) && $this->getCouponBatchResourceService()->isCouponTarget($coupon['batchId'], $target['id']);
    }

    /**
     * @return CouponBatchResourceService
     */
    protected function getCouponBatchResourceService()
    {
        return $this->biz->service('Coupon:CouponBatchResourceService');
    }
}
