<?php

namespace Codeages\Biz\Order\Dao\Impl;

use Codeages\Biz\Order\Dao\OrderItemRefundDao;
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
                'created_time'
            ),
            'serializes' => array(
            ),
            'conditions' => array(
                'title LIKE :title_LIKE',
                'target_id = :target_id',
                'target_type = :target_type'
            )
        );
    }
}