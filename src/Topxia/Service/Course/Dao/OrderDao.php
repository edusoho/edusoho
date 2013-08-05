<?php

namespace Topxia\Service\Course\Dao;

interface OrderDao
{

	public function getOrder($id);

	public function getOrderBySn($sn);

	public function addOrder($order);

	public function updateOrder($id, $fields);

}