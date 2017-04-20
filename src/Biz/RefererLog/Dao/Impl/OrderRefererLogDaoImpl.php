<?php

namespace Biz\RefererLog\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\RefererLog\Dao\OrderRefererLogDao;

class OrderRefererLogDaoImpl extends GeneralDaoImpl implements OrderRefererLogDao
{
    protected $table = 'order_referer_log';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('createdTime', 'recommendedSeq', 'studentNum', 'hitNum'),
            'conditions' => array(
                'id IN ( :ids )',
                'refererLogId = :refererLogId',
                'refererLogId IN (:refererLogIds)',
                'targetId = :targetId',
                'targetType = :targetType',
                'sourceTargetId = :sourceTargetId',
                'sourceTargetType = :sourceTargetType',
                'createdTime >= :startTime',
                'createdTime <= :endTime',
            ),
        );
    }

    public function searchOrderRefererLogs($conditions, $orderBy, $start, $limit, $groupBy)
    {
        $seachFields = '*';
        if (!empty($groupBy)) {
            $seachFields = 'id,orderId,targetId,targetType,COUNT(id) AS buyNum';
        }

        $builder = $this->createQueryBuilder($conditions)
            ->select($seachFields)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->addGroupBy($groupBy);

        foreach ($orderBy ?: array() as $field => $direction) {
            $builder->addOrderBy($field, $direction);
        }

        return $builder->execute()->fetchAll() ?: array();
    }

    public function countOrderRefererLogs($conditions, $groupBy)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('COUNT(id)')
            ->addGroupBy($groupBy);

        return $builder->execute()->fetchColumn(0);
    }

    public function countDistinctOrderRefererLogs($conditions, $distinctField)
    {
        $builder = $this->createQueryBuilder($conditions, array())
            ->select("COUNT(DISTINCT({$distinctField}))");

        return $builder->execute()->fetchColumn(0);
    }
}
