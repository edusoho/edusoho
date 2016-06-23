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

    public function searchOrderRefererLogs($conditions, $orderBy, $start, $limit)
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

    public function searchOrderRefererLogCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');

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
            ->andWhere('refererLogId = :refererLogId')
            ->andWhere('targetId = :targetId')
            ->andWhere('targetType = :targetType');

        return $builder;
    }
}
