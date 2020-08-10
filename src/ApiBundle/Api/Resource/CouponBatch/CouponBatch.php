<?php

namespace ApiBundle\Api\Resource\CouponBatch;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Coupon\CouponException;
use Biz\Coupon\Service\CouponBatchService;

class CouponBatch extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = $this->fillConditions($request->query->all());
        $total = $this->getCouponBatchService()->searchBatchsCount($conditions);
        $couponBatches = $this->getCouponBatchService()->searchBatchs(
            $conditions,
            $this->getSort($request),
            $offset,
            $limit
        );

        foreach ($couponBatches as &$couponBatch) {
            $couponBatch['target'] = $this->getCouponBatchService()->getTargetByBatchId($couponBatch['id']);
            $couponBatch['targetDetail'] = $this->getCouponBatchService()->getCouponBatchTargetDetail($couponBatch['id']);
        }

        return $this->makePagingObject(array_values($couponBatches), $total, $offset, $limit);
    }

    protected function fillConditions($conditions)
    {
        if (isset($conditions['name'])) {
            $conditions['nameLike'] = $conditions['name'];
            unset($conditions['name']);
        }

        if (isset($conditions['unexpired'])) {
            $conditions['unexpiredTime'] = time() - 86400;
            unset($conditions['unexpired']);
        }

        return $conditions;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $batchToken)
    {
        $couponBatch = $this->getCouponBatchService()->getBatchByToken($batchToken);
        $batchAfterFill = $this->getCouponBatchService()->fillUserCurrentCouponByBatches([$couponBatch]);
        if (empty($couponBatch)) {
            throw CouponException::NOTFOUND_COUPON();
        }
        $couponBatch['target'] = $this->getCouponBatchService()->getTargetByBatchId($couponBatch['id']);
        $couponBatch['targetDetail'] = $this->getCouponBatchService()->getCouponBatchTargetDetail($couponBatch['id']);
        $couponBatch['currentUserCoupon'] = empty($batchAfterFill[$couponBatch['id']]['currentUserCoupon']) ? null : $batchAfterFill[$couponBatch['id']]['currentUserCoupon'];

        return $couponBatch;
    }

    /**
     * @return CouponBatchService
     */
    private function getCouponBatchService()
    {
        return $this->service('Coupon:CouponBatchService');
    }

    private function getCouponService()
    {
        return $this->service('Coupon:CouponService');
    }
}
