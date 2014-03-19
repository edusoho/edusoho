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

    public function sumOrderPriceByCourseIdAndStatuses($courseId, array $statuses)
    {
        if(empty($statuses)) {
            return array();
        }

        $marks = str_repeat('?,', count($statuses) - 1) . '?';
        $sql = "SELECT sum(price) FROM {$this->table} WHERE courseId = ? AND status in ({$marks})";

        return $this->getConnection()->fetchColumn($sql, array_merge(array($courseId), $statuses));
    }

}