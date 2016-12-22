<?php

namespace Biz\Order\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Order\Dao\OrderDao;

class OrderDaoImpl extends GeneralDaoImpl implements OrderDao
{
    protected $table = 'orders';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array('data' => 'json'),
            'orderbys'   => array('createdTime', 'recommendedSeq', 'studentNum', 'hitNum'),
            'conditions' => array(
                'sn = :sn',
                'targetType = :targetType',
                'targetId = :targetId',
                'userId = :userId',
                'amount > :amount',
                'totalPrice >= totalPrice',
                'totalPrice > :totalPriceGreaterThan',
                'coinAmount > :coinAmount',
                'status = :status',
                'status <> :statusPaid',
                'status <> :statusCreated',
                'payment = :payment',
                'payment <> :cashPayment',
                'createdTime >= :createdTimeGreaterThan',
                'paidTime >= :paidStartTime',
                'paidTime < :paidEndTime',
                'createdTime >= :startTime',
                'createdTime < :endTime',
                'createdTime < :createdTime_LT',
                'title LIKE :title',
                'targetType IN ( :targetTypes)',
                'updatedTime >= :updatedTime_GE ',
                'userId IN ( :userIds)',
                'status IN ( :includeStatus)',
                'totalPrice > :totalPrice_GT'
            )
        );
    }

    public function getBySn($sn, $lock = false)
    {
        $forUpdate = "";

        if ($lock) {
            $forUpdate = "FOR UPDATE";
        }

        $sql = "SELECT * FROM {$this->table} WHERE sn = ? LIMIT 1 {$forUpdate}";

        return $this->db()->fetchAssoc($sql, array($sn));
    }

    public function getByToken($token)
    {
        return $this->getByFields(array('token' => $token));
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findBySns(array $sns)
    {
        return $this->findInField('sn', $sns);
    }

    public function searchBill($conditions, $orderBy, $start, $limit)
    {
        if (!isset($conditions['startTime'])) {
            $conditions['startTime'] = 0;
        }

        $sql = "SELECT * FROM {$this->table} WHERE `createdTime`>={$conditions['startTime']} AND `createdTime`<{$conditions['endTime']} AND `userId` = {$conditions['userId']} AND (not(`payment` in ('none','coin'))) AND `status` = 'paid' ORDER BY {$orderBy[0]} {$orderBy[1]}  LIMIT {$start}, {$limit}";
        return $this->db()->fetchAll($sql, array());
    }

    public function countBill($conditions)
    {
        if (!isset($conditions['startTime'])) {
            $conditions['startTime'] = 0;
        }

        $sql = "SELECT count(*) FROM {$this->table} WHERE `createdTime`>={$conditions['startTime']} AND `createdTime`<{$conditions['endTime']} AND `userId` = {$conditions['userId']} AND (not(`payment` in ('none','coin'))) AND `status` = 'paid' ";
        return $this->db()->fetchColumn($sql, array());
    }

    public function sumOrderAmounts($startTime, $endTime, array $courseId)
    {
        if (empty($courseId)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseId) - 1).'?';

        $sql = "SELECT  targetId,sum(amount) as  amount from {$this->table} WHERE  createdTime > ? AND createdTime < ? AND targetId IN ({$marks}) AND targetType = 'course' AND status = 'paid' group by targetId";
        return $this->db()->fetchAll($sql, array_merge(array($startTime, $endTime), $courseId));
    }

    protected function _createQueryBuilder($conditions)
    {
        if (isset($conditions["title"])) {
            $conditions["title"] = '%'.$conditions["title"]."%";
        }
        return parent::_createQueryBuilder($conditions);
    }

    public function sumOrderPriceByTargetAndStatuses($targetType, $targetId, array $statuses)
    {
        if (empty($statuses)) {
            return array();
        }

        $marks = str_repeat('?,', count($statuses) - 1).'?';
        $sql   = "SELECT sum(amount) FROM {$this->table} WHERE targetType =? AND targetId = ? AND status in ({$marks})";

        return $this->db()->fetchColumn($sql, array_merge(array($targetType, $targetId), $statuses));
    }

    public function sumCouponDiscountByOrderIds($orderIds)
    {
        if (empty($orderIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($orderIds) - 1).'?';
        $sql   = "SELECT sum(couponDiscount) FROM {$this->table} WHERE id in ({$marks})";
        return $this->db()->fetchColumn($sql, $orderIds);
    }

    public function analysisCourseOrderDataByTimeAndStatus($startTime, $endTime, $status)
    {
        $sql = "SELECT count(id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`createdTime`>=? AND `createdTime`<=? AND `status`=? AND targetType='course' group by date_format(from_unixtime(`createdTime`),'%Y-%m-%d') order by date ASC ";
        return $this->db()->fetchAll($sql, array($startTime, $endTime, $status));
    }

    public function analysisPaidCourseOrderDataByTime($startTime, $endTime)
    {
        $sql = "SELECT count(id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`createdTime`>=? AND `createdTime`<=? AND `status`='paid' AND targetType='course'  AND `amount`>0 group by date_format(from_unixtime(`createdTime`),'%Y-%m-%d') order by date ASC ";
        return $this->db()->fetchAll($sql, array($startTime, $endTime));
    }

    public function analysisPaidClassroomOrderDataByTime($startTime, $endTime)
    {
        $sql = "SELECT count(id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`createdTime`>=? AND `createdTime`<=? AND `status`='paid' AND targetType='classroom'  AND `amount`>0 group by date_format(from_unixtime(`paidTime`),'%Y-%m-%d') order by date ASC ";
        return $this->db()->fetchAll($sql, array($startTime, $endTime));
    }

    public function analysisAmount($conditions)
    {
        $builder = $this->_createQueryBuilder($conditions)
            ->select('sum(amount)');
        return $builder->execute()->fetchColumn(0);
    }

    public function analysisCoinAmount($conditions)
    {
        $builder = $this->_createQueryBuilder($conditions)
            ->select('sum(coinAmount)');
        return $builder->execute()->fetchColumn(0);
    }

    public function analysisTotalPrice($conditions)
    {
        $builder = $this->_createQueryBuilder($conditions)
            ->select('sum(totalPrice)');
        return $builder->execute()->fetchColumn(0);
    }

    public function analysisAmountDataByTime($startTime, $endTime)
    {
        $sql = "SELECT sum(amount) as count, from_unixtime(paidTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`paidTime`>= ?  AND `paidTime`<= ? AND `status`='paid'  group by from_unixtime(`paidTime`,'%Y-%m-%d') order by date ASC ";
        return $this->db()->fetchAll($sql, array($startTime, $endTime));
    }

    public function analysisCourseAmountDataByTime($startTime, $endTime)
    {
        $sql = "SELECT sum(amount) as count, from_unixtime(paidTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`paidTime`>={$startTime} AND `paidTime`<={$endTime} AND `status`='paid' AND targetType='course'   group by from_unixtime(`paidTime`,'%Y-%m-%d') order by date ASC ";
        return $this->db()->fetchAll($sql);
    }

    public function analysisClassroomAmountDataByTime($startTime, $endTime)
    {
        $sql = "SELECT sum(amount) as count, from_unixtime(paidTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`paidTime`>={$startTime} AND `paidTime`<={$endTime} AND `status`='paid' AND targetType='classroom'   group by from_unixtime(`paidTime`,'%Y-%m-%d') order by date ASC ";
        return $this->db()->fetchAll($sql);
    }

    public function analysisVipAmountDataByTime($startTime, $endTime)
    {
        $sql = "SELECT sum(amount) as count, from_unixtime(paidTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`paidTime`>={$startTime} AND `paidTime`<={$endTime} AND `status`='paid' AND targetType='vip'   group by from_unixtime(`paidTime`,'%Y-%m-%d') order by date ASC ";
        return $this->db()->fetchAll($sql);
    }

    public function analysisAmountsDataByTime($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->_createQueryBuilder($conditions)
            ->select("from_unixtime(paidTime,'%Y-%m-%d') date, sum(amount) as count")
            ->groupBy("from_unixtime(`paidTime`,'%Y-%m-%d')")
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll(0) ?: array();
    }

    public function analysisAmountsDataByTitle($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->_createQueryBuilder($conditions)
            ->select('sum(amount) as count, userId, title, targetType, targetId')
            ->groupBy('title')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll(0) ?: array();
    }

    public function analysisAmountsDataByUserId($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->_createQueryBuilder($conditions)
            ->select('sum(amount) as count, userId, title, targetType, targetId')
            ->groupBy('userId')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll(0) ?: array();
    }

    public function analysisExitCourseOrderDataByTime($startTime, $endTime)
    {
        $sql = "SELECT count(id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`createdTime`>={$startTime} AND `createdTime`<={$endTime} AND `status`<>'paid' AND `status`<>'created' AND targetType='course' group by from_unixtime(`createdTime`,'%Y-%m-%d') order by date ASC ";

        return $this->db()->fetchAll($sql);
    }

    public function analysisPaidOrderGroupByTargetType($startTime, $groupBy)
    {
        $sql = "SELECT targetType , count(targetType) as value FROM orders  WHERE STATUS = 'paid' and `totalPrice` >0 and `paidTime`>={$startTime}  GROUP BY targetType";
        return $this->db()->fetchAll($sql);
    }

    public function analysisOrderDate($conditions)
    {
        $builder = $this->_createQueryBuilder($conditions)
            ->select("count(id) as count ,from_unixtime(paidTime,'%Y-%m-%d') date")
            ->groupBy('date');
        return $builder->execute()->fetchAll(0) ?: array();
    }

}
