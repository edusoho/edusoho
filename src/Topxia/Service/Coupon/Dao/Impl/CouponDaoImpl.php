<?php

namespace Topxia\Service\Coupon\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Coupon\Dao\CouponDao;
use PDO;

class CouponDaoImpl extends BaseDao implements CouponDao
{
    protected $table = 'coupon';

    public function searchCoupons($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchCouponsCount(array $conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }
    
    public function addCoupons($coupons)
    {
        if(empty($coupons)){ return array(); }
        $couponsForSQL = array();
        foreach ($coupons as $value) {
            $couponsForSQL = array_merge($couponsForSQL, array_values($value));
        }

        $sql = "INSERT INTO $this->table (code, type, rate, batchId, deadline, targetType, createdTime)  VALUE ";
        for ($i=0; $i < count($coupons); $i++) {
            $sql .= "(?, ?, ?, ?, ?, ?, ?),";
        }

        $sql = substr($sql, 0, -1);

        return $this->getConnection()->executeUpdate($sql, $couponsForSQL);
    }

    public function deleteCouponsByBatch($id)
    {
        return $this->getConnection()->delete($this->table, array('batchId' => $id));
    }

    private function _createSearchQueryBuilder($conditions)
    {   

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'coupon')
            ->andWhere('targetId = :targetId')
            ->andWhere('targetType = :targetType')
            ->andWhere('batchId = :batchId')
            ->andWhere('type = :type');

        return $builder;
    }

}