<?php

namespace Biz\Goods\Dao\Impl;

use Biz\Goods\Dao\GoodsSpecsDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class GoodsSpecsDaoImpl extends GeneralDaoImpl implements GoodsSpecsDao
{
    protected $table = 'goods_specs';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
                'images' => 'json',
                'authority' => 'json',
            ],
            'conditions' => [
                'id = :id',
                'goodsId = :goodsId',
                'title = :title',
                'title LIKE :titleLike',
                'periodType = :periodType',
            ],
            'orderbys' => ['id'],
        ];
    }

    public function findByGoodsId($goodsId)
    {
        return $this->findByFields(['goodsId' => $goodsId]);
    }
}
