<?php

namespace Biz\RewardPointProduct\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\RewardPointProduct\Dao\ProductOrderDao;

class ProductOrderDaoImpl extends GeneralDaoImpl implements ProductOrderDao
{
    protected $table = 'reward_point_product_order';

    public function findByProductId($productId)
    {
        return $this->getByFields(array('productId' => $productId));
    }

    public function findByUserId($userId)
    {
        return $this->getByFields(array('userId' => $userId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('updatedTime', 'createdTime'),
            'orderbys' => array('id', 'sendTime', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'status = :status',
                'userId = :userId',
                'productId = :productId',
                'title = :title',
                'sn = :sn',
                'userId IN ( :userIds)',
                'productId IN ( :productIds)',
            ),
        );
    }
}
