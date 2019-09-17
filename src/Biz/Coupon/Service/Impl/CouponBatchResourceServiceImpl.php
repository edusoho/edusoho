<?php

namespace Biz\Coupon\Service\Impl;

use Biz\BaseService;
use Biz\Coupon\Service\CouponBatchResourceService;

class CouponBatchResourceServiceImpl extends BaseService implements CouponBatchResourceService
{
    private function getCouponBatchResourceDao()
    {
        return $this->createDao('Coupon:CouponBatchResourceDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
