<?php

namespace MarketingMallBundle\Biz\ProductGoodsRelation\Service;

interface ProductGoodsRelationService
{
    public function getProductGoodsRelationByGoodsCode($code);

    public function createProductGoodsRelation($relation);

    public function deleteProductGoodsRelation($id);

    public function getProductGoodsRelationByProductTypeAndProductId($ProductType, $ProductId);
}
