<?php

namespace Codeages\Biz\Order\Dao\Impl;

use Codeages\Biz\Order\Dao\OrderLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class OrderLogDaoImpl extends GeneralDaoImpl implements OrderLogDao
{
    protected $table = 'biz_order_log';

    public function findOrderLogsByOrderId($orderId)
    {
        return $this->findByFields(array('order_id' => $orderId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array(
                'deal_data' => 'json'
            ),
        );
    }
}