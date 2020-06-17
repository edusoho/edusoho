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
}
