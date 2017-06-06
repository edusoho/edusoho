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
