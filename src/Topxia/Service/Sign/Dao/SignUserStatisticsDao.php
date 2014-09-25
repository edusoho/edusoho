<?php

namespace Topxia\Service\Sign\Dao;

interface SignUserStatisticsDao
{
	public function addStatistics($Statistics);

	public function updateStatistics($userId, $targetType, $targetId, $fields);

	public function getStatistics($userId, $targetType, $targetId);
}