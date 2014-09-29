<?php

namespace Topxia\Service\Schedule\Dao;

interface ScheduleDao
{
	public function addSchedule($schedule);

	public function deleteOneDaySchedules($classId, $day);

	public function findScheduleByPeriod($classId, $startDay, $endDay);
}