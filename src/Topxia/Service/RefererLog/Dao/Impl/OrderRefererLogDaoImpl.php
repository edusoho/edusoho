<?php
namespace Topxia\Service\RefererLog\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\RefererLog\Dao\OrderRefererLogDao;

class OrderRefererLogDaoImpl extends BaseDao implements OrderRefererLogDao
{
    protected $table = 'order_referer_log';

    public function getOrderRefererLog($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function addOrderRefererLog($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert order referer log category error.');
        }
        return $this->getOrderRefererLog($this->getConnection()->lastInsertId());
    }

    public function updateOrderRefererLog($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getOrderRefererLog($id);
    }

    public function deleteOrderRefererLog($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function searchOrderRefererLogs($conditions, $orderBy, $start, $limit, $groupBy)
    {
        $this->filterStartLimit($start, $limit);

        $seachFields = '*';
        if (!empty($groupBy)) {
            $seachFields = 'id,orderId,targetId,targetType,COUNT(id) AS buyNum';
        }

        $builder = $this->_createSearchQueryBuilder($conditions, $orderBy, $groupBy)
            ->select($seachFields)
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchOrderRefererLogCount($conditions, $groupBy)
    {
        $builder = $this->_createSearchQueryBuilder($conditions, array('createdTime', 'DESC'), $groupBy)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchDistinctOrderRefererLogCount($conditions, $distinctField)
    {
        $builder = $this->_createSearchQueryBuilder($conditions, array('createdTime', 'DESC'), array())
            ->select("COUNT(DISTINCT({$distinctField}))");

        return $builder->execute()->fetchColumn(0);
    }

    protected function _createSearchQueryBuilder($conditions, $orderBy, $groupBy)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value === '' || is_null($value)) {
                return false;
            }
            return true;
        });

        $builder = $this->createDynamicQueryBuilder($conditions, $orderBy, $groupBy)
            ->from($this->table, $this->table)
            ->andWhere("id IN ( :ids )")
            ->andWhere('refererLogId = :refererLogId')
            ->andWhere('refererLogId IN (:refererLogIds)')
            ->andWhere('targetId = :targetId')
            ->andWhere('targetType = :targetType')
            ->andWhere('sourceTargetId = :sourceTargetId')
            ->andWhere('sourceTargetType = :sourceTargetType')
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
