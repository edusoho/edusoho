<?php

namespace Topxia\Service\Order\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Order\Dao\OrderDao;
use PDO;

class OrderDaoImpl extends BaseDao implements OrderDao
{
    protected $table = 'orders';

    private $serializeFields = array(
        'data' => 'json',
    );

    public function getOrder($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        $order = $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
        return $order ? $this->createSerializer()->unserialize($order, $this->serializeFields) : null;
    
    }

    public function getOrderBySn($sn)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sn = ? LIMIT 1";
        $order = $this->getConnection()->fetchAssoc($sql, array($sn));
        return $order ? $this->createSerializer()->unserialize($order, $this->serializeFields) : null;
    }

    public function findOrdersByIds(array $ids)
    {
        if(empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        $orders = $this->getConnection()->fetchAll($sql, $ids);
        return $this->createSerializer()->unserializes($orders, $this->serializeFields);
    }

    public function addOrder($order)
    {
        $order = $this->createSerializer()->serialize($order, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $order);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert order error.');
        }
        return $this->getOrder($this->getConnection()->lastInsertId());
    }

    public function updateOrder($id, $fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
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
        $orders = $builder->execute()->fetchAll() ? : array(); 
        return $this->createSerializer()->unserializes($orders, $this->serializeFields);
    }

    public function searchBill($conditions, $orderBy, $start, $limit)
    {
        if (!isset($conditions['startTime'])) $conditions['startTime'] = 0;
        $sql = "SELECT * FROM {$this->table} WHERE `createdTime`>={$conditions['startTime']} and `createdTime`<{$conditions['endTime']} and `userId` = {$conditions['userId']} and (not(`payment` in ('none','coin'))) and `status` = 'paid' ORDER BY {$orderBy[0]} {$orderBy[1]}  LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    public function countUserBillNum($conditions)
    {
        if (!isset($conditions['startTime'])) $conditions['startTime'] = 0;
        $sql = "SELECT count(*) FROM {$this->table} WHERE `createdTime`>={$conditions['startTime']} and `createdTime`<{$conditions['endTime']} and `userId` = {$conditions['userId']} and (not(`payment` in ('none','coin'))) and `status` = 'paid' ";
        return $this->getConnection()->fetchColumn($sql, array());
    }    

    public function searchOrderCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    
    public function sumOrderAmounts($startTime,$endTime,array $courseId)
    {
         if(empty($courseId)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseId) - 1) . '?';

        $sql = "SELECT  targetId,sum(amount) as  amount from {$this->table} WHERE  createdTime > ? AND createdTime < ? AND targetId IN ({$marks}) AND targetType = 'course' AND status = 'paid' group by targetId";
        return $this->getConnection()->fetchAll($sql, array_merge(array($startTime, $endTime), $courseId));
    }

    private function _createSearchQueryBuilder($conditions)
    {
        if(isset($conditions["title"])) {
            $conditions["title"] = '%' . $conditions["title"] . "%";
        }
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'course_order')
            ->andWhere('sn = :sn')
            ->andWhere('targetType = :targetType')
            ->andWhere('targetId = :targetId')
            ->andWhere('userId = :userId')
            ->andWhere('amount > :amount')
            ->andWhere('status = :status')
            ->andWhere('status <> :statusPaid')
            ->andWhere('status <> :statusCreated')
            ->andWhere('payment = :payment')
            ->andWhere('createdTime >= :createdTimeGreaterThan')
            ->andWhere('paidTime >= :paidStartTime')
            ->andWhere('paidTime < :paidEndTime')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime < :endTime')           
            ->andWhere('title LIKE :title');
    }

    public function sumOrderPriceByTargetAndStatuses($targetType, $targetId, array $statuses)
    {
        if(empty($statuses)) {
            return array();
        }

        $marks = str_repeat('?,', count($statuses) - 1) . '?';
        $sql = "SELECT sum(amount) FROM {$this->table} WHERE targetType =? AND targetId = ? AND status in ({$marks})";

        return $this->getConnection()->fetchColumn($sql, array_merge(array($targetType, $targetId), $statuses));
    }

    public function sumCouponDiscountByOrderIds($orderIds)
    {
        if (empty($orderIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($orderIds) - 1) . '?';
        $sql = "SELECT sum(couponDiscount) FROM {$this->table} WHERE id in ({$marks})";
        return $this->getConnection()->fetchColumn($sql, $orderIds);
    }

    public function analysisCourseOrderDataByTimeAndStatus($startTime,$endTime,$status)
    {
        $sql="SELECT count(id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`createdTime`>=? and `createdTime`<=? and `status`=? and targetType='course' group by date_format(from_unixtime(`createdTime`),'%Y-%m-%d') order by date ASC ";
        return $this->getConnection()->fetchAll($sql, array($startTime, $endTime, $status));
    }

    public function analysisPaidCourseOrderDataByTime($startTime,$endTime)
    {
        $sql="SELECT count(id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`createdTime`>=? and `createdTime`<=? and `status`='paid' and targetType='course'  and `amount`>0 group by date_format(from_unixtime(`createdTime`),'%Y-%m-%d') order by date ASC ";
        return $this->getConnection()->fetchAll($sql, array($startTime, $endTime));
    }

    public function analysisAmount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('sum(amount)');
        return $builder->execute()->fetchColumn(0);
    }

    public function analysisAmountDataByTime($startTime,$endTime)
    {
        $sql="SELECT sum(amount) as count, from_unixtime(paidTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`paidTime`>= ?  and `paidTime`<= ? and `status`='paid'  group by from_unixtime(`paidTime`,'%Y-%m-%d') order by date ASC ";
        return $this->getConnection()->fetchAll($sql, array($startTime, $endTime));
    }

    public function analysisCourseAmountDataByTime($startTime,$endTime)
    {
        $sql="SELECT sum(amount) as count, from_unixtime(paidTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`paidTime`>={$startTime} and `paidTime`<={$endTime} and `status`='paid' and targetType='course'   group by from_unixtime(`paidTime`,'%Y-%m-%d') order by date ASC ";
        return $this->getConnection()->fetchAll($sql);
    }

    public function analysisExitCourseOrderDataByTime($startTime,$endTime)
    {
        $sql="SELECT count(id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`createdTime`>={$startTime} and `createdTime`<={$endTime} and `status`<>'paid' and `status`<>'created' and targetType='course' group by from_unixtime(`createdTime`,'%Y-%m-%d') order by date ASC ";

        return $this->getConnection()->fetchAll($sql);
    }

} 