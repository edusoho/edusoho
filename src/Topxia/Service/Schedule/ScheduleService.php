<?php
namespace Topxia\Service\Schedule;

interface ScheduleService
{
	public function addSchedule($schedule);

	public function saveSchedules($schedules);

	public function deleteOneDaySchedules($classId, $day);

	public function findScheduleLessonsByWeek($classId, $sunDay);

	public function findScheduleLessonsByMonth($classId, $period);

}