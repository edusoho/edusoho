<?php

namespace Biz\RefererLog\Dao\Impl;

use Biz\RefererLog\Dao\RefererLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class RefererLogDaoImpl extends GeneralDaoImpl implements RefererLogDao
{
    protected $table = 'referer_log';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('createdTime', 'recommendedSeq', 'studentNum', 'hitNum'),
            'conditions' => array(
                'targetType = :targetType',
                'targetId = :targetId',
                'targetId IN (:targetIds)',
                'id IN (:ids)',
                'targetInnerType = :targetInnerType',
                'createdTime >= :startTime',
                'token = :token',
                'ip = :ip',
                'createdTime <= :endTime',
            ),
        );
    }

    public function findRefererLogsGroupByTargetId($targetType, $orderBy, $startTime, $endTime, $start, $limit)
    {
        $parameters = array($targetType, $targetType);
        $sql = "SELECT a.targetId AS targetId, b.hitNum AS hitNum,b.orderCount AS orderCount
                    FROM (SELECT targetId FROM {$this->table} WHERE targetType = ?
                GROUP BY targetId) AS a LEFT JOIN (SELECT targetId, COUNT(id) AS hitNum, SUM(orderCount) AS orderCount FROM {$this->table}
                WHERE targetType = ?";

        if (!empty($startTime)) {
            $sql .= 'AND createdTime >= ?';
            $parameters[] = $startTime;
        }

        if (!empty($endTime)) {
            $sql .= 'and createdTime <= ?';
            $parameters[] = $endTime;
        }
        $sql .= "GROUP BY targetId) AS b ON a.targetId = b.targetId ORDER BY {$orderBy[0]} {$orderBy[1]},targetId DESC";
        $sql = $this->sql($sql, array(), $start, $limit);

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function analysisSummary($conditions)
    {
        $groupBy = 'refererName';
        $builder = $this->createQueryBuilder($conditions, $groupBy)
            ->select('refererName, count(targetId) as count, sum(orderCount) as orderCount')
            ->addOrderBy('count', 'DESC');

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchAnalysisSummaryList($conditions, $groupBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createQueryBuilder($conditions, $groupBy)
            ->select("{$groupBy}, count(id) as count , sum(orderCount) as orderCount")
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->addOrderBy('count', 'DESC');

        return $builder->execute()->fetchAll() ?: array();
    }

    public function countDistinctLogsByField($conditions, $field)
    {
        // @TODO SQL Inject
        $builder = $this->createQueryBuilder($conditions)
            ->select("COUNT(DISTINCT {$field})");

        return $builder->execute()->fetchColumn(0);
    }

    protected function createQueryBuilder($conditions, $groupBy = null)
    {
        $builder = parent::createQueryBuilder($conditions);

        if (!empty($groupBy)) {
            $builder->groupBy($groupBy);
        }

        return $builder;
    }
}
