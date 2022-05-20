<?php

namespace MarketingMallBundle\Biz\ProductGoodsRelation\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ProductGoodsRelationDao extends GeneralDaoInterface
{
    public function getByProductTypeAndProductId($productType, $productId);

    public function getByGoodsCode($code);
}
