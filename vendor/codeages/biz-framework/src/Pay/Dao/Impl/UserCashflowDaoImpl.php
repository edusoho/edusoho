<?php

namespace Codeages\Biz\Framework\Pay\Dao\Impl;

use Codeages\Biz\Framework\Pay\Dao\UserCashflowDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserCashflowDaoImpl extends GeneralDaoImpl implements UserCashflowDao
{
    protected $table = 'biz_user_cashflow';

    public function findByTradeSn($sn)
    {
        return $this->findByFields(array('trade_sn' => $sn));
    }

    public function sumColumnByConditions($column, $conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("sum({$column})");
        return $builder->execute()->fetchColumn(0);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time'),
            'orderbys' => array(
                'id',
                'created_time',
            ),
            'conditions' => array(
                'id = :id',
                'sn = :sn',
                'user_id = :user_id',
                'type = :type',
                'amount > :amount_GT',
                'amount >= :amount_GTE',
                'amount < :amount_LT',
                'amount <= :amount_LTE',
                'currency = :currency',
                'order_sn = :order_sn',
                'trade_sn = :trade_sn',
                'platform = :platform',
                'user_type = :user_type',
                'amount_type = :amount_type',
                'created_time > :created_time_GT',
                'created_time >= :created_time_GTE',
                'created_time < :created_time_LT',
                'created_time <= :created_time_LTE',
            ),
        );
    }
}