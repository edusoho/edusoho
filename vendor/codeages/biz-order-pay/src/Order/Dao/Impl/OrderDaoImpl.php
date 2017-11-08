<?php

namespace Codeages\Biz\Order\Dao\Impl;

use Codeages\Biz\Framework\Dao\DaoException;
use Codeages\Biz\Order\Dao\OrderDao;
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

    public function findBySns(array $orderSns)
    {
        return $this->findInField('sn', $orderSns);
    }

    public function count($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('COUNT(*)');

        return (int) $builder->execute()->fetchColumn(0);
    }

    public function search($conditions, $orderBys, $start, $limit)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $declares = $this->declares();
        foreach ($orderBys ?: array() as $order => $sort) {
            $this->checkOrderBy($order, $sort, $declares['orderbys']);
            $builder->addOrderBy($order, $sort);
        }

        return $builder->execute()->fetchAll();
    }

    public function sumPaidAmount($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("sum(`pay_amount`) as payAmount, sum(`paid_cash_amount`) as cashAmount, sum(`paid_coin_amount`) as coinAmount");

        return $builder->execute()->fetch();  
    }

    public function sumGroupByDate($column, $conditions, $sort, $dateColumn = 'pay_time')
    {
        if (!$this->isSumColumnAllow($column)) {
            throw $this->createDaoException('column is not allowed');
        }

        if (!$this->isDateColumnAllow($dateColumn)) {
            throw $this->createDaoException('dateColumn is not allowed');
        }

        if (!in_array(strtoupper($sort), array('ASC', 'DESC'), true)) {
            throw $this->createDaoException("SQL order by direction is only allowed `ASC`, `DESC`, but you give `{$sort}`.");
        }

        $builder = $this->createQueryBuilder($conditions)
            ->select("sum({$column}) as count ,from_unixtime({$dateColumn},'%Y-%m-%d') date")
            ->groupBy("date {$sort}");

        return $builder->execute()->fetchAll(0) ?: array();
    }

    public function countGroupByDate($conditions, $sort, $dateColumn = 'pay_time')
    {
        if (!$this->isDateColumnAllow($dateColumn)) {
            throw $this->createDaoException('dateColumn is not allowed');
        }

        if (!in_array(strtoupper($sort), array('ASC', 'DESC'), true)) {
            throw $this->createDaoException("SQL order by direction is only allowed `ASC`, `DESC`, but you give `{$sort}`.");
        }

        $builder = $this->createQueryBuilder($conditions)
            ->select("count(id) as count ,from_unixtime({$dateColumn},'%Y-%m-%d') date")
            ->groupBy("date {$sort}");

        return $builder->execute()->fetchAll(0) ?: array();
    }

    private function isDateColumnAllow($column)
    {
        $whiteList = $this->dateColumnWhiteList();

        if (in_array($column, $whiteList)) {
            return true;
        }
        return false;
    }

    private function isSumColumnAllow($column)
    {
        $whiteList = $this->sumColumnWhiteList();

        if (in_array($column, $whiteList)) {
            return true;
        }
        return false;
    }

    private function sumColumnWhiteList()
    {
        return array('amount', 'pay_amount', 'price_amount');
    }

    private function dateColumnWhiteList()
    {
        return array('pay_time');
    }

    private function checkOrderBy($order, $sort, $allowOrderBys)
    {
        if (!in_array($order, $allowOrderBys, true)) {
            throw $this->createDaoException(
                sprintf("SQL order by field is only allowed '%s', but you give `{$order}`.", implode(',', $allowOrderBys))
            );
        }
        if (!in_array(strtoupper($sort), array('ASC', 'DESC'), true)) {
            throw $this->createDaoException("SQL order by direction is only allowed `ASC`, `DESC`, but you give `{$sort}`.");
        }
    }

    private function createDaoException($message = '', $code = 0)
    {
        return new DaoException($message, $code);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array(
                'pay_data' => 'json',
                'callback' => 'json',
                'create_extra' => 'json',
                'success_data' => 'json',
                'fail_data' => 'json',
            ),
            'orderbys' => array(
                'id',
                'created_time',
            ),
            'conditions' => array(
                'id IN (:ids)',
                'sn = :sn',
                'user_id = :user_id',
                'payment = :payment',
                'created_time < :created_time_LT',
                'pay_time < :pay_time_LT',
                'pay_time > :pay_time_GT',
                'pay_amount > :pay_amount_GT',
                'price_amount > :price_amount_GT',
                'source = :source', 
                'status = :status',
                'status IN (:statuses)',
                'seller_id = :seller_id',
                'created_time >= :start_time',
                'created_time <= :end_time',
                'title LIKE :title_like',
                'updated_time >= :updated_time_GE',
                'refund_deadline < :refund_deadline_LT',
            ),
        );
    }
}
