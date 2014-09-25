<?php

namespace Topxia\Service\Sign\Dao;

interface SignUserStatisticsDao
{
	public function addStatistics($Statistics);

	public function updateStatistics($id, $fields);

	public function getStatistics($userId, $targetType, $targetId);
}