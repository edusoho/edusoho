<?php

namespace Biz\Goods\Dao\Impl;

use Biz\Goods\Dao\GoodsDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class GoodsDaoImpl extends GeneralDaoImpl implements GoodsDao
{
    protected $table = 'goods';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
                'images' => 'json',
            ],
            'conditions' => [
                'id = :id',
                'id IN (:ids)',
                'productId = :productId',
                'title = :title',
                'title LIKE :titleLike',
            ],
            'orderbys' => ['id'],
        ];
    }

    public function getByProductId($productId)
    {
        return $this->getByFields(['productId' => $productId]);
    }
}
