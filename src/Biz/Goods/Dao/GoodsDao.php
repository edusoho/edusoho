<?php

namespace Biz\Goods\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface GoodsDao extends AdvancedDaoInterface
{
    public function getByProductId($productId);

    public function findByIds($ids);

    public function findByProductIds(array $productIds);

    public function refreshHotSeq();
}
