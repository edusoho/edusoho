<?php

namespace MarketingMallBundle\Biz\ProductMallGoodsRelation\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Dao\ProductMallGoodsRelationDao;

class ProductMallGoodsRelationDaoImpl extends GeneralDaoImpl implements ProductMallGoodsRelationDao
{
    protected $table = 'product_mall_goods_relation';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [],
            'conditions' => [
                'id = :id',
                'productId = :productId',
                'productId IN (:productIds)',
                'productType = :productType',
                'goodsCode = :goodsCode',
            ],
            'orderbys' => ['id'],
        ];
    }

    public function getClassroomIds($ids)
    {
        $sql = "SELECT productId FROM {$this->table} where productId in ({$ids}) AND productType = 'classroom'";

        return $this->db()->fetchAll($sql) ?: array();
    }

    public function getByProductTypeAndProductId($productType, $productId)
    {
        return $this->getByFields(['productType' => $productType, 'productId' => $productId]);
    }

    public function getByGoodsCode($code)
    {
        return $this->getByFields(['goodsCode' => $code]);
    }

    public function findByProductType($productType)
    {
        return $this->findByFields(['productType' => $productType]);
    }
}
