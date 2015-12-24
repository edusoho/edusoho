<?php

namespace Topxia\Service\Coupon\CouponProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Card\DetailProcessor\DetailProcessor;

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

    protected function getCouponService()
    {
        return ServiceKernel::instance()->createService('Coupon.CouponService');
    }
}
