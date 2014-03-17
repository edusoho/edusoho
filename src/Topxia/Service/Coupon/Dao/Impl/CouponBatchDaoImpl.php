<?php

namespace Topxia\Service\Coupon\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Coupon\Dao\CouponBatchDao;
use PDO;

class CouponBatchDaoImpl extends BaseDao implements CouponBatchDao
{
    protected $table = 'coupon_batch';

    public function getBatch ($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function searchBatchsCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchBatchs($conditions, $orderBy, $start, $limit)
    {
    	$this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function addBatch($batch)
    {
        $affected = $this->getConnection()->insert($this->table, $batch);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert CouponBatch error.');
        }

        return $this->getBatch($this->getConnection()->lastInsertId());
    }

    public function deleteBatch($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function findBatchByPrefix($prefix)
    {
        $sql = "SELECT * FROM {$this->table} WHERE prefix = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($prefix));
    }

    private function _createSearchQueryBuilder($conditions)
    {   
        if (isset($conditions['name'])) {
            $conditions['nameLike'] = "%{$conditions['name']}%";
            unset($conditions['name']);
        }
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'coupon_batch')
            ->andWhere('targetId = :targetId')
            ->andWhere('targetType = :targetType')
            ->andWhere('name LIKE :nameLike')
            ->andWhere('type = :type');

        return $builder;
    }

}