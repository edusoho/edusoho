<?php

namespace Topxia\Service\Sign\Dao;

interface SignTargetStatisticsDao
{
	public function addStatistics($statistics);

	public function updateStatistics($id, $fields);

	public function getStatistics($targetType, $targetId, $date);

	public function getStatisticsById($id);
}	