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

    public function searchRefererLogs($conditions, $orderBy, $start, $limit, $groupBy)
    {
        $this->filterStartLimit($start, $limit);
        $this->checkOrderBy($orderBy, array('createdTime'));

        $searchFields = '*';

        if (!empty($groupBy)) {
            $searchFields = 'id,COUNT(id) AS hitNum,targetId,targetType,SUM(orderCount) AS orderCount,createdTime';
        }

        $builder = $this->createQueryBuilder($conditions, $orderBy, $groupBy)
            ->select($searchFields)
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchRefererLogCount($conditions, $groupBy)
    {
        $builder = $this->createQueryBuilder($conditions, array(), $groupBy)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchAnalysisRefererLogSum($conditions, $groupBy)
    {
        $orderBy = array('value', 'DESC');
        $builder = $this->createQueryBuilder($conditions, $orderBy, $groupBy)
            ->select('COUNT(id) as value , r.refererHost as name ');

        return $builder->execute()->fetchAll() ?: array();
    }

    protected function createQueryBuilder($conditions, $orderBy, $groupBy)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->getTable(), 'r')
            ->andWhere('targetType = :targetType')
            ->andWhere('targetId = :targetId')
            ->andWhere('targetId IN (:targetIds)')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime <= :endTime');

        for ($i = 0; $i < count($orderBy); $i = $i + 2) {
            $builder->addOrderBy($orderBy[$i], $orderBy[$i + 1]);
        };
        //  ->orderBy($orderBy[0], $orderBy[1]);
        if (!empty($groupBy)) {
            $builder->groupBy($groupBy);
        }
        return $builder;
    }
}
