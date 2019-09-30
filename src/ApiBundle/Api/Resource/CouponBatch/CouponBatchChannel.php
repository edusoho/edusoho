<?php

namespace ApiBundle\Api\Resource\CouponBatch;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Annotation\ResponseFilter;

class CouponBatchChannel extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\CouponBatch\CouponBatchFilter")
     */
    public function search(ApiRequest $request, $channel)
    {
        switch ($channel) {
            case 'h5Mps':
                return $this->findH5MpsCouponBatches($request);
                break;

            default:
                throw new NotFoundHttpException('channel not found.', null, ErrorCode::RESOURCE_NOT_FOUND);
                break;
        }
    }

    private function findH5MpsCouponBatches($request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $total = $this->getCouponBatchService()->countH5MpsBatches($request->query->all());
        $batches = $this->getCouponBatchService()->searchH5MpsBatches($request->query->all(), $offset, $limit);

        $batches = $this->getCouponBatchService()->fillUserCurrentCouponByBatches($batches);

        foreach ($batches as &$couponBatch) {
            $couponBatch['target'] = $this->getCouponBatchService()->getTargetByBatchId($couponBatch['id']);
            $couponBatch['targetDetail'] = $this->getCouponBatchService()->getCouponBatchTargetDetail($couponBatch['id']);
        }

        return $this->makePagingObject(array_values($batches), $total, $offset, $limit);
    }

    private function getCouponService()
    {
        return $this->service('Coupon:CouponService');
    }

    private function getCouponBatchService()
    {
        return $this->service('Coupon:CouponBatchService');
    }
}
