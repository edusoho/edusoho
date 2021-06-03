<?php

namespace Biz\Goods\Dao\Impl;

use Biz\Goods\Dao\GoodsDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class GoodsDaoImpl extends AdvancedDaoImpl implements GoodsDao
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
                'id NOT IN (:excludeIds)',
                'creator = :creator',
                'categoryId = :categoryId',
                'productId = :productId',
                'productId IN (:productIds)',
                'title = :title',
                'title LIKE :titleLike',
                'status = :status',
                'type = :type',
                'type IN (:types)',
                'id <> :excludeId',
                'price > :price_GT',
                'maxPrice > :maxPrice_GT',
                'maxPrice < :maxPrice_LT',
                'minPrice > :minPrice_GT',
                'minPrice < :minPrice_LT',
                'recommendWeight > :recommendWeight_GT',
                'recommendedTime > :recommendedTime_GT',
            ],
            'orderbys' => ['id', 'hotSeq', 'publishedTime', 'createdTime', 'recommendWeight', 'recommendedTime'],
        ];
    }

    public function getByProductId($productId)
    {
        return $this->getByFields(['productId' => $productId]);
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByProductIds(array $productIds)
    {
        return $this->findInField('productId', $productIds);
    }

    public function findPublishedByProductIds(array $productIds)
    {
        if (empty($productIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($productIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE status = 'published' AND productId IN ({$marks});";

        return $this->db()->fetchAll($sql, $productIds);
    }

    public function refreshHotSeq()
    {
        $sql = "UPDATE {$this->table} set hotSeq = 0;";

        return $this->db()->exec($sql);
    }
}
