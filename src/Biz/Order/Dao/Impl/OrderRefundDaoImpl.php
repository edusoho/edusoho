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
            'orderbys'   => array(),
            'conditions' => array(
                'status = :status',
                'userId = :userId',
                'targetType = :targetType',
                'orderId = :orderId',
                'targetType = :targetType',
                'userId IN ( :userIds )',
                'targetId = :targetId',
                'status <> :statusNotEqual',
                'targetId IN ( :targetIds )'
            )
        );
    }


    public function countByUserId($userId)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE userId = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId));
    }

    public function findByUserId($userId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($userId)) ?: array();
    }


    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere('status = :status')
            ->andWhere('userId = :userId')
            ->andWhere('targetType = :targetType')
            ->andWhere('orderId = :orderId')
            ->andWhere('targetType = :targetType')
            ->andWhere('userId IN ( :userIds )')
            ->andWhere('targetId = :targetId')
            ->andWhere('status <> :statusNotEqual')
            ->andWhere('targetId IN ( :targetIds )');


        if (isset($conditions['targetIds'])) {
            $builder->andWhere("targetId IN ( :targetIds )");
        }

        return $builder;
    }

    protected function _createQueryBuilder($conditions)
    {
        $builder = parent::_createQueryBuilder($conditions);

        if (isset($conditions['targetIds'])) {
            $builder->andWhere("targetId IN ( :targetIds )");
        }
        return $builder;
    }

    public function findByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findByOrderId($orderId)
    {
        $sql = " SELECT * FROM {$this->table} WHERE orderId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($orderId));
    }

}