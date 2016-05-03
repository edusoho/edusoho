<?php

namespace Topxia\Service\Coupon\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Coupon\Dao\CouponDao;

class CouponDaoImpl extends BaseDao implements CouponDao
{
    protected $table = 'coupon';

    public function getCoupon($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function getCouponsByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function getCouponByCode($code, $lock = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = ? LIMIT 1".($lock ? ' FOR UPDATE' : '');

        return $this->getConnection()->fetchAssoc($sql, array($code)) ?: null;
    }

    public function updateCoupon($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getCoupon($id);
    }

    public function findCouponsByBatchId($batchId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM  $this->table WHERE batchId = {$batchId} ORDER BY createdTime DESC LIMIT {$start} , {$limit}";

        return $this->getConnection()->fetchAll($sql);
    }

    public function findCouponsByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';

        $that = $this;
        $keys = implode(',', $ids);
        return $this->fetchCached("ids:{$keys}", $marks, $ids, function ($marks, $ids) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id IN ({$marks});";

            return $that->getConnection()->fetchAll($sql, $ids);
        }

        );
    }


    public function searchCoupons($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('*')
                        ->setFirstResult($start)
                        ->setMaxResults($limit);
        if(!empty($orderBy)) {
            $length = count($orderBy)/2;
            for ($i=0; $i < $length; $i++) { 
                $index = $i*2;
                $builder->orderBy($orderBy[$index], $orderBy[$index+1]);
            }
        }

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchCouponsCount(array $conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addCoupon($coupon)
    {
        $affected = $this->getConnection()->insert($this->table, $coupon);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert Coupon error.');
        }

        return $this->getCoupon($this->getConnection()->lastInsertId());
    }

    public function deleteCouponsByBatch($id)
    {
        return $this->getConnection()->delete($this->table, array('batchId' => $id));
    }

    private function _createSearchQueryBuilder($conditions)
    {
        $tmpConditions = array();

        if (isset($conditions['batchIdNotEqual'])) {
            $tmpConditions['batchIdNotEqual'] = $conditions['batchIdNotEqual'];
        }

        if (!isset($conditions['userId'])) {
            $conditions = array_filter($conditions);
        }

        $conditions = array_merge($conditions, $tmpConditions);

        if (isset($conditions['code'])) {
            $conditions['codeLike'] = "%{$conditions['code']}%";
            unset($conditions['code']);
        }

        if (isset($conditions['startDateTime'])) {
            $conditions['startDateTime'] = strtotime($conditions['startDateTime']."\n00:00:00");
        }

        if (isset($conditions['endDateTime'])) {
            $conditions['endDateTime'] = strtotime($conditions['endDateTime']."+1 day");
        }

        if (isset($conditions['useStartDateTime'])) {
            $conditions['useStartDateTime'] = strtotime($conditions['useStartDateTime']."\n00:00:00");
        }

        if (isset($conditions['useEndDateTime'])) {
            $conditions['useEndDateTime'] = strtotime($conditions['useEndDateTime']."+1 day");
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
                        ->from($this->table, 'coupon')
                        ->andWhere('userId = :userId')
                        ->andWhere('targetId = :targetId')
                        ->andWhere('targetType = :targetType')
                        ->andWhere('batchId = :batchId')
                        ->andWhere('batchId <> :batchIdNotEqual')
                        ->andWhere('type = :type')
                        ->andWhere('status = :status')
                        ->andWhere('createdTime >= :startDateTime')
                        ->andWhere('createdTime < :endDateTime')
                        ->andWhere('code LIKE :codeLike')
                        ->andWhere('orderTime >= :useStartDateTime')
                        ->andWhere('orderTime < :useEndDateTime')
                        ->andWhere('id IN ( :ids)');

        return $builder;
    }
}
