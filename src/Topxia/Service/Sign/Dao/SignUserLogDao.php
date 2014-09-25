<?php

namespace Topxia\Service\Sign\Dao;

interface SignUserLogDao
{
	public function addSignLog($signLog);

	public function getSignLOg($id);

	public function findSignLogByPeriod($userId, $targetType, $targetId, $startTime, $EndTime);
}