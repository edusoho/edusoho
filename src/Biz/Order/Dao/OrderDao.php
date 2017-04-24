<?php

namespace Biz\Order\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderDao extends GeneralDaoInterface
{
    public function getBySn($sn);

    public function getByToken($token);

    public function findByIds(array $ids);

    public function findBySns(array $sns);

    public function countBill($conditions);

    public function sumOrderAmounts($startTime, $endTime, array $courseId);

    public function sumOrderPriceByTargetAndStatuses($targetType, $targetId, array $statuses);

    public function sumCouponDiscountByOrderIds($orderIds);

    public function analysisCourseOrderDataByTimeAndStatus($startTime, $endTime, $status);

    public function analysisPaidCourseOrderDataByTime($startTime, $endTime);

    public function analysisPaidClassroomOrderDataByTime($startTime, $endTime);

    public function analysisAmount($conditions);

    public function analysisTotalPrice($conditions);

    public function analysisAmountDataByTime($startTime, $endTime);

    public function analysisCourseAmountDataByTime($startTime, $endTime);

    public function analysisClassroomAmountDataByTime($startTime, $endTime);

    public function analysisVipAmountDataByTime($startTime, $endTime);

    public function analysisExitCourseOrderDataByTime($startTime, $endTime);

    public function analysisPaidOrderGroupByTargetType($startTime, $groupBy);

    public function analysisOrderDate($conditions);

    public function searchBill($conditions, $orderBy, $start, $limit);

    public function countUserBill($conditions);
}
