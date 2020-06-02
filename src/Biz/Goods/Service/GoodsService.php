<?php

namespace Biz\Goods\Service;

interface GoodsService
{
    public function getGoods($id);

    public function createGoods($goods);

    public function updateGoods($id, $goods);

    public function deleteGoods($id);

    public function searchGoods($conditions, $orderBys, $start, $limit, $columns = []);
}
