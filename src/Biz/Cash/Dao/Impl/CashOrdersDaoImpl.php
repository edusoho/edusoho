<?php

namespace Biz\Cash\Dao\Impl;

use Biz\Cash\Dao\CashOrdersDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CashOrdersDaoImpl extends GeneralDaoImpl implements CashOrdersDao
{
    protected $table = 'cash_orders';

    public function getBySn($sn, array $options = array())
    {
        $lock = isset($options['lock']) && $options['lock'] === true;
        $sql = "SELECT * FROM {$this->table} WHERE sn = ?  LIMIT 1".($lock ? ' FOR UPDATE' : '');

        return $this->db()->fetchAssoc($sql, array($sn)) ?: array();
    }

    public function getByToken($token)
    {
        return $this->getByFields(array(
            'token' => $token,
        ));
    }

    public function closeOrders($time)
    {
        $sql = "UPDATE {$this->table} set status ='cancelled' WHERE status = 'created' AND createdTime < ?";

        return $this->db()->executeUpdate($sql, array($time));
    }

    public function analysisAmount($conditions)
    {
        return $this
            ->createQueryBuilder($conditions)
            ->select('sum(amount)')
            ->execute()
            ->fetchColumn(0);
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'data' => 'json',
            ),
            'orderbys' => array(
                'createdTime',
            ),
            'conditions' => array(
                'status = :status',
                'userId = :userId',
                'payment = :payment',
                'title = :title',
                'createdTime >= :startTime',
                'createdTime < :endTime',
                'sn = :sn',
            ),
        );
    }
}
