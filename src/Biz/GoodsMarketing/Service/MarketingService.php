<?php

namespace Biz\GoodsMarketing\Service;

interface MarketingService
{
    public function getMeans($id);

    public function createMeans($means);

    public function updateMeans($id, $means);

    public function deleteMeans($id);

    public function countMeans($conditions);

    public function searchMeans($conditions, $orderBys, $start, $limit, $columns = []);

    public function findValidMeansByTargetTypeAndTargetId($targetType, $targetId);
}
