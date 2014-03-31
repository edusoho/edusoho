<?php

namespace Topxia\Service\Order\Dao;

interface OrderLogDao
{

	public function getLog($id);

	public function addLog($log);

	public function findLogsByOrderId($orderId);

}