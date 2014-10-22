<?php

namespace Topxia\Service\Schedule\Dao;

interface ScheduleDao
{
	public function addSchedule($schedule);

	public function deleteOneDaySchedules($classId, $day);

	public function findSchedulesByClassIdAndPeriod($classId, $startDay, $endDay);

	public function findSchedulesByPeriod($startDay, $endDay);
}