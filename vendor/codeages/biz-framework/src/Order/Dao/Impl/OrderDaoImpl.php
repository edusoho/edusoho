<?php

namespace Codeages\Biz\Framework\Order\Dao\Impl;

use Codeages\Biz\Framework\Dao\DaoException;
use Codeages\Biz\Framework\Order\Dao\OrderDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Util\ArrayToolkit;

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
        $conditions = $this->handleConditions($conditions);

        $builder = $this->createQueryBuilder($conditions)
            ->select('COUNT(*)');

        return (int) $builder->execute()->fetchColumn(0);
    }

    public function search($conditions, $orderBys, $start, $limit)
    {
        $conditions = $this->handleConditions($conditions);
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

    public function sumGroupByDate($column, $conditions, $sort, $dateColumn = 'pay_time')
    {
        $conditions = $this->handleConditions($conditions);
        $builder = $this->createQueryBuilder($conditions)
            ->select("sum({$column}) as count ,from_unixtime({$dateColumn},'%Y-%m-%d') date")
            ->groupBy("date {$sort}");

        return $builder->execute()->fetchAll(0) ?: array();
    }

    public function countGroupByDate($conditions, $sort, $dateColumn = 'pay_time')
    {
        $conditions = $this->handleConditions($conditions);
        $builder = $this->createQueryBuilder($conditions)
            ->select("count(id) as count ,from_unixtime({$dateColumn},'%Y-%m-%d') date")
            ->groupBy("date {$sort}");

        return $builder->execute()->fetchAll(0) ?: array();
    }

    private function handleConditions($conditions)
    {
        $customKeys = array('order_item_title', 'order_item_target_ids', 'order_item_target_type', 'trade_payment');
        foreach ($conditions as $key => $value) {
            if (in_array($key, $customKeys)) {
                $customConditions[$key] = $value;
                unset($conditions[$key]);
            }
        }

        if (!empty($customConditions)) {
            //设置0的目的是为了当搜索出数量为0的时候，搜索出来的数据为0
            $conditions['ids'] = array(0);

            $itemConditionsString = '';
            if(!empty($customConditions['order_item_title'])) {
                $itemConditionsString .= "AND title LIKE '%{$customConditions['order_item_title']}%'";
            }

            if (!empty($customConditions['order_item_target_ids'])) {
                if (!is_array($customConditions['order_item_target_ids'])) {
                    throw $this->createDaoException("column order_item_target_ids mast be array");
                }
                $targetIdMarks = implode(',', $customConditions['order_item_target_ids']);
                $itemConditionsString .= "AND target_id IN ({$targetIdMarks})";
            }

            if (!empty($customConditions['order_item_target_type'])) {
                $itemConditionsString .= "AND target_type = '{$customConditions['order_item_target_type']}'";
            }

            if (!empty($itemConditionsString)) {
                $itemConditionsString = ltrim($itemConditionsString, 'AND');
                $itemSql = "SELECT order_id FROM `biz_order_item` WHERE {$itemConditionsString}";
                $itemResult = $this->db()->fetchAll($itemSql);
                if (!empty($itemResult)) {
                    $ids = ArrayToolkit::column($itemResult, 'order_id');
                    $conditions['ids'] = $ids;
                }

            }
        }
        return $conditions;
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
                'create_extra' => 'json'
            ),
            'orderbys' => array(
                'id',
                'created_time'
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
                'status = :status',
                'seller_id = :seller_id',
                'created_time >= :start_time',
                'created_time <= :end_time',
                'title LIKE :title_like',
            )
        );
    }
}