<?php

namespace Topxia\Service\Sign\Dao;

interface SignUserLogDao
{
	public function addSignLog($signLog);

	public function getSignLog($id);

	public function updateSignLog($id, $fields);

	public function findSignLogByPeriod($userId, $targetType, $targetId, $startTime, $EndTime);
}