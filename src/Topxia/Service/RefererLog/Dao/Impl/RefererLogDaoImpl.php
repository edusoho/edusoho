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

    public function searchRefererLogs($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy($orderBy[0], $orderBy[1]);
        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchRefererLogCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchRefererLogsGroupByTargetId($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('id,COUNT(id) AS hitNum,targetId,targetType,SUM(orderCount) AS orderCount,createdTime')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy($orderBy[0], $orderBy[1])
            ->groupBy('targetId');

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchRefererLogCountGroupByTargetId($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)')
            ->groupBy('targetId');

        return $builder->execute()->fetchColumn(0);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value === '' || is_null($value)) {
                return false;
            }
            return true;
        });

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere("id IN ( :ids )")
            ->andWhere('targetId = :targetId')
            ->andWhere('targetId IN (:targetIds)')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime <= :endTime')
            ->andWhere('targetType = :targetType');

        return $builder;
    }
}
