<?php

namespace Biz\RewardPoint\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\RewardPoint\Dao\ProductOrderDao;

class ProductOrderDaoImpl extends GeneralDaoImpl implements ProductOrderDao
{
    protected $table = 'reward_point_product_order';

    public function findByProductId($productId)
    {
        return $this->findByFields(array('productId' => $productId));
    }

    public function findByUserId($userId)
    {
        return $this->findByFields(array('userId' => $userId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'sendTime', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'status = :status',
                'userId = :userId',
                'productId = :productId',
                'title = :title',
                'title LIKE :titleLike',
                'sn = :sn',
                'userId IN ( :userIds)',
                'productId IN ( :productIds)',
                'createdTime >= :startDate',
                'createdTime <= :endDate',
            ),
        );
    }
}
