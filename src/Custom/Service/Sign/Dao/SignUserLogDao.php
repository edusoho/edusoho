<?php

namespace Custom\Service\Sign\Dao;

interface SignUserLogDao
{
	public function addSignLog($signLog);

	public function getSignLog($id);

	public function updateSignLog($id, $fields);

    public function getSignByUserId($userId);

	public function findSignLogByPeriod($userId, $targetType, $targetId, $startTime, $EndTime);
}