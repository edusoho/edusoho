<?php

namespace Topxia\Service\Classes\Dao;

interface ClassSignStatisticsDao
{
	public function addClassSignStatistics($classSignStatistics);

	public function getClassSignStatisticsById($id);

	public function updateClassSignStatistics($classId, $fields);

	public function getClassSignStatisticsByClassId($classId);

}