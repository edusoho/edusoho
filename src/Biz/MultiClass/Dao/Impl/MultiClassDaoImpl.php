<?php

namespace Biz\MultiClass\Dao\Impl;

use Biz\MultiClass\Dao\MultiClassDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MultiClassDaoImpl extends GeneralDaoImpl implements MultiClassDao
{
    protected $table = 'multi_class';

    public function findByProductIds(array $productIds)
    {
        return $this->findInField('productId', array_values($productIds));
    }

    public function findByProductId($productId)
    {
        return $this->findByFields(['productId' => $productId]);
    }

    public function getByTitle($title)
    {
        return $this->getByFields(['title' => $title]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
            'conditions' => [
                'id = :id',
                'id IN ( :ids)',
                'productId = :productId',
                'courseId IN ( :courseIds)',
                'copyId = :copyId',
            ],
        ];
    }
}
