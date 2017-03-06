<?php

namespace Biz\Coupon\CouponProcessor;

use Biz\Card\DetailProcessor\DetailProcessor;
use Biz\Coupon\Service\CouponService;
use Topxia\Service\Common\ServiceKernel;

class CouponDetailProcessor implements DetailProcessor
{
    public function getDetailById($id)
    {
        return $this->getCouponService()->getCoupon($id);
    }

    public function getCardDetailsByCardIds($ids)
    {
        return $this->getCouponService()->getCouponsByIds($ids);
    }

    /**
     * @return CouponService
     */
    protected function getCouponService()
    {
        return ServiceKernel::instance()->getBiz()->service('Coupon:CouponService');
    }
}
