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
		return $this->fetch($id);
	}

	public function getOrderBySn($sn)
	{
        return $this->createQueryBuilder()
            ->select('*')->from($this->table, 'corder')
            ->where("sn = :sn")
            ->setParameter(":sn", $sn)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);
	}

	public function addOrder($order)
	{
        $id = $this->insert($order);
    	return $this->getOrder($id);
	}

	public function updateOrder($id, $fields)
	{
		$this->update($id, $fields);
		return $this->getOrder($id);
	}
    
    public function searchOrders($conditions, $orderBy, $start, $limit)
    {
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