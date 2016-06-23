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
