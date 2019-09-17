<?php

namespace Biz\Coupon\Service\Impl;

use Biz\BaseService;
use Biz\Coupon\Dao\CouponBatchResourceDao;
use Biz\Coupon\Service\CouponBatchResourceService;

class CouponBatchResourceServiceImpl extends BaseService implements CouponBatchResourceService
{
    public function countCouponBatchResource($conditions)
    {
        return $this->getCouponBatchResourceDao()->count($conditions);
    }

    public function searchCouponBatchResource($conditions, $orderBy, $start, $limit)
    {
        return $this->getCouponBatchResourceDao()->search($conditions, $orderBy, $start, $limit);
    }

    /**
     * @return CouponBatchResourceDao
     */
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
