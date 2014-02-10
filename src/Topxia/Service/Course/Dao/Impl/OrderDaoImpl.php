<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\OrderDao;
use PDO;

class OrderDaoImpl extends BaseDao implements OrderDao
{
    protected $table = 'course_order';

    public function getOrder($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

	public function getOrderBySn($sn)
	{
        $sql = "SELECT * FROM {$this->table} WHERE sn = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($sn));
	}

    public function findOrdersByIds(array $ids)
    {
        if(empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function getOrdersByPromoCode($code)
    {
        $sql = "SELECT * FROM {$this->table} WHERE promoCode = ? ";
        return $this->getConnection()->fetchAll($sql, array($code));
    }

    public function findOrdersByPromoCodes(array $codes)
    {
        if(empty($codes)) {
            return array();
        }

        $marks = str_repeat('?,', count($codes) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE promoCode IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $codes);
    }


    public function getOrdersBymTookeen($mTookeen)
    {
        $sql = "SELECT * FROM {$this->table} WHERE mTookeen = ? ";
        return $this->getConnection()->fetchAll($sql, array($mTookeen));
    }

    public function findOrdersBymTookeens(array $mTookeens)
    {
        if(empty($mTookeens)) {
            return array();
        }

        $marks = str_repeat('?,', count($mTookeens) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE mTookeen IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $mTookeens);
    }

	public function addOrder($order)
	{
        $affected = $this->getConnection()->insert($this->table, $order);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert order error.');
        }
        return $this->getOrder($this->getConnection()->lastInsertId());
	}

	public function updateOrder($id, $fields)
	{
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
		return $this->getOrder($id);
	}
    
    public function searchOrders($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchOrderCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function _createSearchQueryBuilder($conditions)
    {
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'course_order')
            ->andWhere('sn = :sn')
            ->andWhere('courseId = :courseId')
            ->andWhere('userId = :userId')
            ->andWhere('status = :status')
            ->andWhere('payment = :payment')
            ->andWhere('paidTime >= :paidStartTime')
            ->andWhere('paidTime < :paidEndTime');
    }

}