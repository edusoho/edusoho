<?php

namespace Biz\Coupon\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CouponBatchDao extends GeneralDaoInterface
{
    public function findBatchsByIds($ids);

    public function getBatchByToken($token, $locked = false);

    public function findBatchByPrefix($prefix);

    public function sumDeductAmountByBatchId($batchId);

    public function searchH5MpsBatches($conditions, $offset, $limit);

    public function countH5MpsBatches($conditions);
}
