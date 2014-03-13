<?php

namespace Topxia\Service\Course\Dao;

interface OrderDao
{

	public function getOrder($id);

	public function getOrderBySn($sn);

	public function findOrdersByIds(array $ids);

	public function addOrder($order);

	public function updateOrder($id, $fields);

    public function searchOrders($conditions, $orderBy, $start, $limit);

    public function searchOrderCount($conditions);

    public function sumOrderPriceByCourseIdAndStatuses($courseId, array $statuses);

}