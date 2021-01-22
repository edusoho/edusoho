<?php

namespace Biz\RefererLog\Dao\Impl;

use Biz\RefererLog\Dao\OrderRefererLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class OrderRefererLogDaoImpl extends GeneralDaoImpl implements OrderRefererLogDao
{
    protected $table = 'order_referer_log';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'orderbys' => ['createdTime', 'recommendedSeq', 'studentNum', 'hitNum'],
            'conditions' => [
                'id IN ( :ids )',
                'refererLogId = :refererLogId',
                'refererLogId IN (:refererLogIds)',
                'targetId = :targetId',
                'targetType = :targetType',
                'sourceTargetId = :sourceTargetId',
                'sourceTargetType = :sourceTargetType',
                'createdTime >= :startTime',
                'createdTime <= :endTime',
            ],
        ];
    }

    public function searchOrderRefererLogs($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('targetId,targetType,COUNT(id) AS buyNum')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->addGroupBy('targetId,targetType');

        foreach ($orderBy ?: [] as $field => $direction) {
            $builder->addOrderBy($field, $direction);
        }

        return $builder->execute()->fetchAll() ?: [];
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
        $builder = $this->createQueryBuilder($conditions, [])
            ->select("COUNT(DISTINCT({$distinctField}))");

        return $builder->execute()->fetchColumn(0);
    }
}
