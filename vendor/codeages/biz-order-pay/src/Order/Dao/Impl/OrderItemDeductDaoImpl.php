<?php

namespace Codeages\Biz\Order\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;
use Codeages\Biz\Order\Dao\OrderItemDeductDao;

class OrderItemDeductDaoImpl extends GeneralDaoImpl implements OrderItemDeductDao
{
    protected $table = 'biz_order_item_deduct';

    public function findByItemId($itemId)
    {
        return $this->findByFields(array(
            'item_id' => $itemId
        ));
    }

    public function findByOrderId($orderId)
    {
        return $this->findByFields(array(
            'order_id' => $orderId
        ));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array(
                'snapshot' => 'json',
            ),
        );
    }
}