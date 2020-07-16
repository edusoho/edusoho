<?php

namespace Biz\Goods\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface GoodsDao extends GeneralDaoInterface
{
    public function getByProductId($productId);

    public function findByIds($ids);

    public function findByProductIds(array $productIds);
}
