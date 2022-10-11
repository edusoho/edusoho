<?php

namespace MarketingMallBundle\Biz\ProductMallGoodsRelation\Service;

interface ProductMallGoodsRelationService
{
    public function getProductMallGoodsRelationByGoodsCode($code);

    public function createProductMallGoodsRelation($relation);

    public function deleteProductMallGoodsRelation($id);

    public function getProductMallGoodsRelationByProductTypeAndProductId($ProductType, $ProductId);

    public function findProductMallGoodsRelationsByProductType($productType);

    public function findProductMallGoodsRelationsByProductIdsAndProductType($productIds, $productType);

    public function checkMallClassroomCourseExist($courseId);

    public function checkEsProductCanDelete(array $productIds, $type);

    public function getExistClassroomIds(array $classroomIds);
}
