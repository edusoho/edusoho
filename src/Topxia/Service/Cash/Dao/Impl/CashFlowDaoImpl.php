<?php

namespace Topxia\Service\Cash\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Cash\Dao\CashFlowDao;

class CashFlowDaoImpl extends BaseDao implements CashFlowDao
{
    protected $table = 'cash_flow';

    public function getFlow($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getFlowBySn($sn)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sn = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($sn)) ? : null;
    }

    public function getFlowByOrderSn($orderSn)
    {
        $sql = "SELECT * FROM {$this->table} WHERE orderSn = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($orderSn)) ? : null;
    }

    public function searchFlows($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->createFlowQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }
    
    public function analysisAmount($conditions)
    {
        $builder = $this->createFlowQueryBuilder($conditions)
            ->select('sum(amount)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchFlowsCount($conditions)
    {
        $builder = $this->createFlowQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addFlow($flow)
    {
        $affected = $this->getConnection()->insert($this->table, $flow);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert cash flow error.');
        }
        return $this->getFlow($this->getConnection()->lastInsertId());
    }

    public function updateFlow($flow)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getFlow($id);
    }

    public function findUserIdsByFlows($type,$createdTime, $orderBy, $start, $limit)
    {
        $sql="SELECT  userId,sum(amount) as amounts FROM `cash_flow` where ".($type ? "`type`=? and " : "" )." createdTime >= ? group by userId  order by amounts {$orderBy} limit {$start},{$limit} ";
        
        return $this->getConnection()->fetchAll($sql,$type ? array($type,$createdTime) : array($createdTime) ) ? : array() ;
    }

    public function findUserIdsByFlowsCount($type,$createdTime)
    {
        $sql="SELECT count( distinct userId)  FROM `cash_flow` where ".($type ? "`type`=? and " : "" )." createdTime >= ? ";
      
        return $this->getConnection()->fetchColumn($sql,$type ? array($type,$createdTime) : array($createdTime)) ? : 0 ;
    }

    private function createFlowQueryBuilder($conditions)
    {

        $conditions = array_filter($conditions);
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'cash_flow')
            ->andWhere('userId = :userId')
            ->andWhere('type = :type')
            ->andWhere('cashType = :cashType')
            ->andWhere('status = :status')
            ->andWhere('category = :category')
            ->andWhere('sn = :sn')
            ->andWhere('name = :name')
            ->andWhere('orderSn = :orderSn')
            ->andWhere('createdTime >= :startTime') 
            ->andWhere('createdTime < :endTime');
    }

}