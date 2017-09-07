<?php

namespace Codeages\Biz\Framework\Order\Dao\Impl;

use Codeages\Biz\Framework\Order\Dao\OrderItemDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class OrderItemDaoImpl extends GeneralDaoImpl implements OrderItemDao
{
    protected $table = 'biz_order_item';

    public function findByOrderId($orderId)
    {
        return $this->findByFields(array(
            'order_id' => $orderId
        ));
    }

    public function findByOrderIds($orderIds)
    {
        return $this->findInField('order_id', $orderIds);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'orderbys' => array(
                'id',
                'created_time'
            ),
            'serializes' => array(
                'create_extra' => 'json'
            ),
            'conditions' => array(
                'order_id IN (:order_ids)',
                'status = :status',
                'target_id IN (:target_ids)',
                'target_id = :target_id',
                'target_type = :target_type',
                'created_time >= :start_time',
                'created_time <= :end_time',
            )
        );
    }
}