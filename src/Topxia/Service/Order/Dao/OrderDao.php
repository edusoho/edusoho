<?php

namespace Topxia\Service\Order\Dao;

interface OrderDao
{

    public function getOrder($id);

    public function getOrderBySn($sn);

    public function findOrdersByIds(array $ids);

    public function addOrder($order);

    public function updateOrder($id, $fields);

    public function searchOrders($conditions, $orderBy, $start, $limit);

    public function searchOrderCount($conditions);

    public function sumOrderPriceByTargetAndStatuses($targetType, $targetId, array $statuses);

    public function sumCouponDiscountByOrderIds($orderIds);

    public function analysisCourseOrderDataByTimeAndStatus($startTime,$endTime,$status);

    public function analysisPaidCourseOrderDataByTime($startTime,$endTime);

    public function analysisAmount($conditions);

    public function analysisAmountDataByTime($startTime,$endTime);

    public function analysisCourseAmountDataByTime($startTime,$endTime);

    public function analysisExitCourseOrderDataByTime($startTime,$endTime);

}