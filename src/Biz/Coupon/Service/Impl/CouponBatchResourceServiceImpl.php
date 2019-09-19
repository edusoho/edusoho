<?php

namespace Biz\Coupon\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Coupon\Dao\CouponBatchResourceDao;
use Biz\Coupon\Service\CouponBatchResourceService;

class CouponBatchResourceServiceImpl extends BaseService implements CouponBatchResourceService
{
    public function findResourcesByBatchId($batchId)
    {
        $resources = $this->getCouponBatchResourceDao()->findByBatchId($batchId);

        return ArrayToolkit::index($resources, 'id');
    }

    public function countCouponBatchResource($conditions)
    {
        return $this->getCouponBatchResourceDao()->count($conditions);
    }

    public function isCouponTarget($batchId, $targetId)
    {
        $count = $this->countCouponBatchResource(array('batchId' => $batchId, 'targetId' => $targetId));

        return empty($count) ? false : true;
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
