<?php
namespace Topxia\Service\RefererLog\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\RefererLog\Dao\RefererLogDao;

class RefererLogDaoImpl extends BaseDao implements RefererLogDao
{
    protected $table = 'referer_log';

    public function addRefererLog($referLog)
    {
        $referLog['createdTime'] = time();
        $referLog['updatedTime'] = $referLog['createdTime'];
        $affected                = $this->getConnection()->insert($this->getTable(), $referLog);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert user error.');
        }

        return $this->getRefererLogById($this->getConnection()->lastInsertId());
    }

    public function getRefererLogById($id)
    {
        $that = $this;
        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} where id =? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        });
    }

    public function waveRefererLog($id, $field, $diff)
    {
        $fields = array('orderCount');

        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }

        $currentTime = time();

        $sql = "UPDATE {$this->table} SET {$field} = {$field} + ?, updatedTime = '{$currentTime}' WHERE id = ? LIMIT 1";

        $result = $this->getConnection()->executeQuery($sql, array($diff, $id));
        $this->clearCached();
        return $this->getRefererLogById($id);
    }

    public function findRefererLogsGroupByTargetId($targetType, $orderBy, $startTime, $endTime, $start, $limit)
    {
        $sql = "SELECT a.targetId, b.hitNum,b.orderCount FROM (SELECT id,targetId from {$this->table} WHERE targetType = ?
                GROUP BY targetId) AS a LEFT JOIN (SELECT id,targetId, COUNT(id) AS hitNum, SUM(orderCount) AS orderCount FROM {$this->table}
                WHERE targetType = ? AND createdTime >= ? and createdTime <= ?
                GROUP BY targetId) AS b ON a.targetId = b.targetId ORDER BY {$orderBy[0]} {$orderBy[1]} LIMIT {$start}, {$limit}";

        return $this->getConnection()->fetchAll($sql, array($targetType, $targetType, $startTime, $endTime));
    }

    public function searchAnalysisSummary($conditions)
    {
        $orderBy = array('count', 'DESC');
        $groupBy = 'refererName';
        $builder = $this->createQueryBuilder($conditions, $orderBy, $groupBy)
            ->select('refererName, count(targetid) as count, sum(ordercount) as orderCount');
        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchAnalysisSummaryList($conditions, $groupBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $orderBy = array('count', 'DESC');
        $builder = $this->createQueryBuilder($conditions, $orderBy, $groupBy)
            ->select("{$groupBy}, count(id) as count , sum(ordercount) as orderCount")
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchAnalysisSummaryListCount($conditions, $field)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("COUNT(DISTINCT {$field})");
        return $builder->execute()->fetchColumn(0);
    }

    public function searchRefererLogCount($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('COUNT(*)')
        ;
        return $builder->execute()->fetchColumn(0);
    }

    public function searchRefererLogs($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit)
        ;
        return $builder->execute()->fetchAll() ?: array();
    }

    protected function createQueryBuilder($conditions, $orderBy = null, $groupBy = null)
    {
        if(!empty($conditions['endTime'])){
            $conditions['endTime'] += 24*3600;
        }
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->getTable(), 'r')
            ->andWhere('targetType = :targetType')
            ->andWhere('targetId = :targetId')
            ->andWhere('targetId IN (:targetIds)')
            ->andWhere('targetInnerType = :targetInnerType')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime <= :endTime');

        for ($i = 0; $i < count($orderBy); $i = $i + 2) {
            $builder->addOrderBy($orderBy[$i], $orderBy[$i + 1]);
        };

        if (!empty($groupBy)) {
            $builder->groupBy($groupBy);
        }
        return $builder;
    }
}
