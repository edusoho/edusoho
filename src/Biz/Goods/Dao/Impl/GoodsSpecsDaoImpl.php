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
                'services' => 'json',
            ],
            'conditions' => [
                'id = :id',
                'goodsId = :goodsId',
                'title = :title',
                'targetId = :targetId',
                'title LIKE :titleLike',
                'status = :status',
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
        $sql = "SELECT * FROM {$this->table} WHERE goodsId = ? ORDER BY `seq` ASC;";

        return $this->db()->fetchAll($sql, [$goodsId]);
    }

    public function findPublishedByGoodsId($goodsId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE goodsId = ? AND status = 'published' ORDER BY `seq` ASC;";

        return $this->db()->fetchAll($sql, [$goodsId]);
    }

    public function deleteByGoodsIdAndTargetId($goodsId, $targetId)
    {
        return $this->db()->delete($this->table, ['goodsId' => $goodsId, 'targetId' => $targetId]);
    }

    public function deleteByGoodsId($goodsId)
    {
        return $this->db()->delete($this->table, ['goodsId' => $goodsId]);
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }
}
