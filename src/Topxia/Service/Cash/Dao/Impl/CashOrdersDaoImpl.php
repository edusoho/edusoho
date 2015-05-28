<?php

namespace Topxia\Service\Cash\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Cash\Dao\CashOrdersDao;

class CashOrdersDaoImpl extends BaseDao implements CashOrdersDao
{   
    protected $table = 'cash_orders';

    public function getOrder($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addOrder($fields)
    {
        $order = $this->getConnection()->insert($this->table, $fields);
        if ($order <= 0) {
            throw $this->createDaoException('Insert cash_orders account error.');
        }
        return $this->getOrder($this->getConnection()->lastInsertId());
    }

    public function getOrderBySn($sn,$lock=false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sn = ?  LIMIT 1" . ($lock ? ' FOR UPDATE' : '');
        return $this->getConnection()->fetchAssoc($sql, array($sn)) ? : null;
    }

    public function updateOrder($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getOrder($id);
    }

    public function closeOrders($time)
    {
        $sql = "UPDATE {$this->table} set status ='cancelled' WHERE status = 'created' and createdTime < ?";
        return $this->getConnection()->executeUpdate($sql, array($time));
    }

    public function searchOrders($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->createOrderQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchOrdersCount($conditions)
    {
        $builder = $this->createOrderQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function analysisAmount($conditions)
    {
        $builder = $this->createOrderQueryBuilder($conditions)
            ->select('sum(amount)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createOrderQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'cash_orders')
            ->andWhere('status = :status')
            ->andWhere('userId = :userId')
            ->andWhere('payment = :payment')
            ->andWhere('title = :title')
            ->andWhere('sn = :sn');
    }

}