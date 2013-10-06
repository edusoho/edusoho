<?php

namespace Topxia\Service\Course\Dao;

interface OrderRefundDao
{
	public function getRefund($id);

	public function findRefundCountByUserId($userId);

	public function findRefundsByUserId($userId, $start, $limit);

	public function searchRefunds($conditions, $orderBy, $start, $limit);

	public function searchRefundCount($conditions);

	public function addRefund($refund);

	public function updateRefund($id, $refund);
}