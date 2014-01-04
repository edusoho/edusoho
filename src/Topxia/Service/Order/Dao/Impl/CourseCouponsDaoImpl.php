<?php

namespace Topxia\Service\Order\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Order\Dao\CourseCouponsDao;
use PDO;

class CourseCouponsDaoImpl extends BaseDao implements CourseCouponsDao
{
    protected $table = 'course_coupon';

    public function searchCourseCouponsCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchCourseCoupons($conditions, $orderBy, $start, $limit)
    {
    	$this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function getCoupon($id)
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
    }

    public function deleteCoupon($couponId)
    {
        return $this->getConnection()->delete($this->table, array('id' => $couponId));
    }

    private function _createSearchQueryBuilder($conditions)
    {
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'course_coupon')
            ->andWhere('orderUserId = :orderUserId')
            ->andWhere('type = :type')
            ->andWhere('code = :code')
            ->andWhere('status = :status');
    }

}