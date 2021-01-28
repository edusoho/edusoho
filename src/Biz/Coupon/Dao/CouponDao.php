<?php

namespace Biz\Coupon\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CouponDao extends GeneralDaoInterface
{
    public function findByIds(array $ids);

    public function getByCode($code, array $options = array());

    public function findByBatchId($batchId, $start, $limit);

    public function deleteByBatch($id);
}
