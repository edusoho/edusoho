<?php

namespace Topxia\Service\Course\Dao;

interface OrderLogDao
{

	public function getLog($id);

	public function addLog($log);

	public function findLogsByOrderId($orderId);

}