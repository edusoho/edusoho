<?php

namespace Codeages\Biz\Pay\Dao\Impl;

use Codeages\Biz\Framework\Dao\DaoException;
use Codeages\Biz\Pay\Dao\CashflowDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CashflowDaoImpl extends GeneralDaoImpl implements CashflowDao
{
    protected $table = 'biz_pay_cashflow';

    public function findByTradeSn($sn)
    {
        return $this->findByFields(array('trade_sn' => $sn));
    }

    /**
     * @param $column
     * @param $conditions
     * @return bool|string
     * @throws DaoException
     */
    public function sumColumnByConditions($column, $conditions)
    {
        if (!$this->isSumColumnAllow($column)) {
            throw new DaoException('column is not allowed');
        }
        $builder = $this->createQueryBuilder($conditions)
            ->select("sum({$column})");
        return $builder->execute()->fetchColumn(0);
    }

    public function countUsersByConditions($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("count(distinct user_id)");
        return $builder->execute()->fetchColumn(0);
    }

    public function sumAmountGroupByUserId($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("sum(amount) as amount, user_id")
            ->groupBy('user_id');

        return $builder->execute()->fetchAll();
    }

    private function sumColumnWhiteList()
    {
        return array('amount');
    }

    protected function isSumColumnAllow($column)
    {
        $whiteList = $this->sumColumnWhiteList();

        if (in_array($column, $whiteList)) {
            return true;
        }
        return false;
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
                'action = :action',
                'user_id != :except_user_id',
                'user_id = :user_id',
                'buyer_id = :buyer_id',
                'user_id IN (:user_ids)',
                'type = :type',
                'title LIKE :title_like',
                'amount > :amount_GT',
                'amount >= :amount_GTE',
                'amount < :amount_LT',
                'amount <= :amount_LTE',
                'currency = :currency',
                'order_sn = :order_sn',
                'trade_sn IN (:trade_sns)',
                'trade_sn = :trade_sn',
                'platform = :platform',
                'amount_type = :amount_type',
                'created_time > :created_time_GT',
                'created_time >= :created_time_GTE',
                'created_time < :created_time_LT',
                'created_time <= :created_time_LTE',
            ),
        );
    }
}
