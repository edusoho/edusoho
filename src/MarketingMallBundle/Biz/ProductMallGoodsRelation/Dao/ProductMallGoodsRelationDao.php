<?php

namespace MarketingMallBundle\Biz\ProductMallGoodsRelation\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ProductMallGoodsRelationDao extends GeneralDaoInterface
{
    public function getByProductTypeAndProductId($productType, $productId);

    public function getByGoodsCode($code);

    public function findByProductType($productType);

    public function getClassroomIds($ids);
}
