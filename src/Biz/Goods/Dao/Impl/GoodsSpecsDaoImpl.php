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
            ],
            'conditions' => [
                'id = :id',
                'goodsId = :goodsId',
                'title = :title',
                'targetId = :targetId',
                'title LIKE :titleLike',
            ],
            'orderbys' => ['id'],
        ];
    }

    public function getByGoodsIdAndTargetId($goodsId, $targetId)
    {
        return $this->getByFields(['goodsId' => $goodsId, 'targetId' => $targetId]);
    }

    public function findByGoodsId($goodsId)
    {
        return $this->findByFields(['goodsId' => $goodsId]);
    }

    public function deleteByGoodsIdAndTargetId($goodsId, $targetId)
    {
        return $this->db()->delete($this->table, ['goodsId' => $goodsId, 'targetId' => $targetId]);
    }

    public function deleteByGoodsId($goodsId)
    {
        return $this->db()->delete($this->table, ['goodsId' => $goodsId]);
    }
}
