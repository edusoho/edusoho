<?php

namespace Codeages\Biz\Framework\Order\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

class OrderItemDeductDaoImpl extends GeneralDaoImpl implements GeneralDaoInterface
{
    protected $table = 'biz_order_item_deduct';

    public function findByItemId($itemId)
    {
        return $this->findByFields(array(
            'item_id' => $itemId
        ));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time')
        );
    }
}