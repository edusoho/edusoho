<?php
namespace Topxia\Service\Schedule;

interface ScheduleService
{
	public function addSchedule($schedule);

	public function saveSchedules($classId, $schedules, $date);

	public function deleteOneDaySchedules($classId, $day);

	public function findScheduleLessonsByWeek($classId, $sunDay);

	public function findScheduleLessonsByMonth($classId, $period);

	public function findOneDaySchedules($classId, $date);

	public function findOneDaySchedulesByUserId($classId, $userId, $date);
}