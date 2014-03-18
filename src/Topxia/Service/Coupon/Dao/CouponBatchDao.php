<?php

namespace Topxia\Service\Coupon\Dao;

interface CouponBatchDao
{

    public function getBatch ($id);

    public function findBatchsByIds($ids);

    public function searchBatchsCount($conditions);

    public function searchBatchs($conditions, $orderBy, $start, $limit);

    public function deleteBatch($id);

    public function addBatch($batch);

    public function findBatchByPrefix($prefix);

}