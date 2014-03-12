<?php

namespace Topxia\Service\Coupon\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Coupon\Dao\CouponDao;
use PDO;

class CouponDaoImpl extends BaseDao implements CouponDao
{
    protected $table = 'coupon_batch';

    public function searchCouponsCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

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

/*    public function getCoupon($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function generateCoupon($coupon)
    {
        $affected = $this->getConnection()->insert($this->table, $coupon);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert coupon error.');
        }
        return $this->getCoupon($this->getConnection()->lastInsertId());
    }*/

    public function deleteCoupon($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
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