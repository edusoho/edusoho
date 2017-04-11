<?php

namespace Biz\Order\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Order\Dao\OrderRefundDao;

class OrderRefundDaoImpl extends GeneralDaoImpl implements OrderRefundDao
{
    protected $table = 'order_refund';

    public function declares()
    {
        return array(
            'timestamps' => array(),
            'serializes' => array(),
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'status = :status',
                'userId = :userId',
                'targetType = :targetType',
                'orderId = :orderId',
                'targetType = :targetType',
                'userId IN ( :userIds )',
                'targetId = :targetId',
                'status <> :statusNotEqual',
                'targetId IN ( :targetIds )',
            ),
        );
    }

    public function countByUserId($userId)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE userId = ?";

        return $this->db()->fetchColumn($sql, array($userId));
    }

    public function findByUserId($userId, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->db()->fetchAll($sql, array($userId)) ?: array();
    }

    protected function createQueryBuilder($conditions)
    {
        $builder = parent::createQueryBuilder($conditions);

        if (isset($conditions['targetIds'])) {
            $builder->andWhere('targetId IN ( :targetIds )');
        }

        return $builder;
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getByOrderId($orderId)
    {
        return $this->getByFields(array('orderId' => $orderId));
    }

    public function findByOrderIds(array $orderIds)
    {
        return $this->findInField('orderId', $orderIds);
    }
}
