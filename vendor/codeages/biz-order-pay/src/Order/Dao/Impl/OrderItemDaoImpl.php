<?php

namespace Codeages\Biz\Order\Dao\Impl;

use Codeages\Biz\Order\Dao\OrderItemDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class OrderItemDaoImpl extends GeneralDaoImpl implements OrderItemDao
{
    protected $table = 'biz_order_item';

    public function findByOrderId($orderId)
    {
        return $this->findByFields(array(
            'order_id' => $orderId,
        ));
    }

    public function findByOrderIds($orderIds)
    {
        return $this->findInField('order_id', $orderIds);
    }

    public function getOrderItemByOrderIdAndTargetIdAndTargetType($orderId, $targetId, $targetType)
    {
        return $this->getByFields(array(
            'order_id' => $orderId,
            'target_type' => $targetType,
            'target_id' => $targetId,
        ));
    }

    public function sumPayAmount($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('sum(`pay_amount`)')
            ->andWhere('target_id = :target_id')
            ->andWhere('target_type = :target_type')
            ->andWhere('pay_time >= :pay_time_GE')
            ->andWhere('pay_time <= :pay_time_LE')
            ->andWhere('status IN (:statuses)')
            ->andWhere('status = :status');

        return (int) $builder->execute()->fetchColumn(0);
    }

    public function findByConditions($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('*');
        return $builder->execute()->fetchAll();
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'orderbys' => array(
                'id',
                'created_time',
            ),
            'serializes' => array(
                'create_extra' => 'json',
                'snapshot' => 'json',
            ),
            'conditions' => array(
                'order_id IN (:order_ids)',
                'status = :status',
                'status IN (:statuses)',
                'target_id IN (:target_ids)',
                'target_id = :target_id',
                'user_id = :user_id',
                'title like :title_LIKE',
                'target_type = :target_type',
                'created_time >= :start_time',
                'created_time <= :end_time',
                'pay_time < :pay_time_LT',
                'pay_time > :pay_time_GT',
                'pay_amount > :pay_amount_GT',
            ),
        );
    }
}
