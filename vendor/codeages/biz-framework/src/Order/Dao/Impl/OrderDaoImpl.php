<?php

namespace Codeages\Biz\Framework\Order\Dao\Impl;

use Codeages\Biz\Framework\Order\Dao\OrderDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class OrderDaoImpl extends GeneralDaoImpl implements OrderDao
{
    protected $table = 'biz_order';

    public function getBySn($sn, array $options = array())
    {
        $lock = isset($options['lock']) && $options['lock'] === true;

        $forUpdate = '';

        if ($lock) {
            $forUpdate = 'FOR UPDATE';
        }

        $sql = "SELECT * FROM {$this->table} WHERE sn = ? LIMIT 1 {$forUpdate}";

        return $this->db()->fetchAssoc($sql, array($sn));
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array(
                'pay_data' => 'json',
                'callback' => 'json',
                'signed_data' => 'json'
            ),
            'orderbys' => array(
                'id',
                'created_time'
            ),
            'conditions' => array(
                'created_time < :created_time_LT',
                'pay_time < :pay_time_LT',
                'status = :status',
                'seller_id = :seller_id',
                'created_time >= :start_time',
                'created_time <= :end_time',
            )
        );
    }
}