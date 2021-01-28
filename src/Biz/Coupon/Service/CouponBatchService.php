<?php

namespace Biz\Coupon\Service;

use Biz\System\Annotation\Log;

interface CouponBatchService
{
    public function getBatch($id);

    public function findBatchsByIds(array $ids);

    public function getBatchByToken($token, $locked = false);

    /**
     * @param $couponData
     *
     * @return mixed
     * @Log(module="coupon",action="generate_coupon")
     */
    public function generateCoupon($couponData);

    public function searchBatchs(array $conditions, $orderBy, $start, $limit);

    public function searchBatchsCount(array $conditions);

    /**
     * @param $id
     *
     * @Log(module="coupon",action="delete_batch")
     */
    public function deleteBatch($id);

    public function checkBatchPrefix($prefix);

    public function receiveCoupon($token, $userId, $canRepeat = false);

    public function updateBatch($id, $fields);

    public function sumDeductAmountByBatchId($batchId);

    public function createBatchCoupons($batchId, $generatedNum = 0);

    public function updateUnreceivedNumByBatchId($batchId);

    public function searchH5MpsBatches($conditions, $offset, $limit);

    public function countH5MpsBatches($conditions);

    public function fillUserCurrentCouponByBatches($batches);

    public function getCouponBatchContent($batchId);
}
