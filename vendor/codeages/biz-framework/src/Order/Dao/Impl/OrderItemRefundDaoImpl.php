<?php

namespace Codeages\Biz\Framework\Order\Dao\Impl;

use Codeages\Biz\Framework\Order\Dao\OrderItemRefundDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class OrderItemRefundDaoImpl extends GeneralDaoImpl implements OrderItemRefundDao
{
    protected $table = 'biz_order_item_refund';

    public function findByOrderRefundId($orderRefundId)
    {
        return $this->findByFields(array(
            'order_refund_id' => $orderRefundId
        ));
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
            ),
            'conditions' => array(
            )
        );
    }
}