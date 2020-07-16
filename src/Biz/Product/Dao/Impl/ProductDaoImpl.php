<?php

namespace Biz\Product\Dao\Impl;

use Biz\Product\Dao\ProductDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ProductDaoImpl extends GeneralDaoImpl implements ProductDao
{
    protected $table = 'product';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [],
            'conditions' => [
                'id = :id',
                'targetId = :targetId',
                'targetType = :targetType',
                'owner = :owner',
                'title LIKE :titleLike',
                'title = :title',
            ],
            'orderbys' => ['id'],
        ];
    }

    public function getByTargetIdAndType($targetId, $targetType)
    {
        return $this->getByFields(['targetId' => $targetId, 'targetType' => $targetType]);
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByTargetTypeAndTargetIds($targetType, array $targetIds)
    {
        $marks = str_repeat('?,', count($targetIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE targetType = ? AND targetId IN ({$marks});";

        return $this->db()->fetchAll($sql, array_merge([$targetType], $targetIds));
    }
}
