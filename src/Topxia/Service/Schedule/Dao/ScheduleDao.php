<?php

namespace Topxia\Service\Schedule\Dao;

interface ScheduleDao
{
	public function addSchedule($schedule);

	public function findScheduleByPeriod($classId, $startDay, $endDay);
}