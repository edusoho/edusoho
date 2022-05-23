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
