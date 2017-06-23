<?php

namespace Biz\RewardPoint\Dao\Impl;

use Biz\RewardPoint\Dao\ProductDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ProductDaoImpl extends GeneralDaoImpl implements ProductDao
{
    protected $table = 'reward_point_product';

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'id = :id',
                'id IN (:ids)',
                'status = :status',
                'title LIKE :titleLike',
            ),
        );
    }
}
