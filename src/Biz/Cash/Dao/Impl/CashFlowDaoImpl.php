<?php

namespace Biz\Cash\Dao\Impl;

use Biz\Cash\Dao\CashFlowDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CashFlowDaoImpl extends GeneralDaoImpl implements CashFlowDao
{
    protected $table = 'cash_flow';

    public function getBySn($sn)
    {
        return $this->getByFields(array(
            'sn' => $sn,
        ));
    }

    public function getByOrderSn($orderSn)
    {
        return $this->getByFields(array(
            'orderSn' => $orderSn,
        ));
    }

    public function analysisAmount($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('sum(amount)');

        return $builder->execute()->fetchColumn(0);
    }

    public function findUserIdsByFlows($type, $createdTime, $orderBy, $start, $limit)
    {
        $conditions = array(
            'type' => $type,
            'startTime' => $createdTime,
        );
        $builder = $this->createQueryBuilder($conditions)
            ->select('userId, sum(amount) as amounts')
            ->groupBy('userId')
            ->addOrderBy('amounts', $orderBy)
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll();
    }

    public function countByTypeAndGTECreatedTime($type, $createdTime)
    {
        $sql = 'SELECT count( distinct userId)  FROM `cash_flow` where '.($type ? '`type`=? AND ' : '').' createdTime >= ? ';

        return $this->db()->fetchColumn($sql, $type ? array($type, $createdTime) : array($createdTime)) ?: 0;
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'userId = :userId',
                'type = :type',
                'cashType = :cashType',
                'status = :status',
                'category = :category',
                'sn = :sn',
                'name = :name',
                'orderSn = :orderSn',
                'createdTime >= :startTime',
                'createdTime < :endTime',
            ),
            'orderbys' => array(
                'id',
                'createdTime',
            ),
        );
    }
}
