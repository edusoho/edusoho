<?php

namespace Topxia\Service\Classes\Dao;

interface ClassMemberSignStatisticsDao
{
	public function addClassMemberSignStatistics($ClassMemberSignStatistics);

	public function getClassMemberSignStatisticsById($id);

	public function updateClassMemberSignStatistics($userId, $classId, $fields);

	public function getClassMemberSignStatistics($userId, $classId);
}