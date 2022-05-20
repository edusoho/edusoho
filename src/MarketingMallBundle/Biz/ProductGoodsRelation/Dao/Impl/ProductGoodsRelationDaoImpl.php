<?php

namespace MarketingMallBundle\Biz\ProductGoodsRelation\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use MarketingMallBundle\Biz\ProductGoodsRelation\Dao\ProductGoodsRelationDao;

class ProductGoodsRelationDaoImpl extends GeneralDaoImpl implements ProductGoodsRelationDao
{
    protected $table = 'product_goods_relation';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [],
            'conditions' => [
                'id = :id',
                'productId = :productId',
                'productType = :productType',
                'goodsCode = :goodsCode',
            ],
            'orderbys' => ['id'],
        ];
    }

    public function getByProductTypeAndProductId($productType, $productId)
    {
        return $this->getByFields(['productType' => $productType, 'productId' => $productId]);
    }

    public function getByGoodsCode($code)
    {
        return $this->getByFields(['goodsCode' => $code]);
    }
}
