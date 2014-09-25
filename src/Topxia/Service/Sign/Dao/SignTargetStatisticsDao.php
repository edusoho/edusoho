<?php

namespace Topxia\Service\Sign\Dao;

interface SignTargetStatisticsDao
{
	public function addStatistics($statistics);

	public function updateStatistics($targetType, $targetId, $fields);

	public function getStatistics($targetType, $targetId);

}