<?php
namespace Topxia\Service\Schedule\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Schedule\ScheduleService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Common\ArrayToolkit;

class ScheduleServiceImpl extends BaseService implements ScheduleService
{
    public function addSchedule($schedule)
    {
        return $this->getScheduleDao()->addSchedule($schedule);
    }

    public function findScheduleLessonsByWeek($classId, $sunday)
    {
        $week = date('w');
        $day = date('Ymd', strtotime('- {$week} days'));
        $sunday = $sunday ? : date('Ymd', strtotime("- {$week} days"));
        $staturday = date('Ymd', strtotime('+' . (6 - $week) . 'days'));
        $schedules =  $this->getScheduleDao()->findScheduleByPeriod($classId, $sunday, $staturday);
        
        return $this->makeUpResult($schedules, $sunday);
    }

    public function findScheduleLessonsByMonth($classId, $yearMonth)
    {
        $startDay = $yearMonth . '01';
        $endDay = $yearMonth . date('t', strtotime($staturday));
        $schedules = $this->getScheduleDao()->findScheduleByPeriod($classId, $startDay, $endDay);
        return $this->makeUpResult($schedules);
    }

    private function makeUpResult($schedules, $sunday)
    {
        $lessonIds = ArrayToolkit::column($schedules?:array(), 'lessonId');
        $lessons = $this->getCourseService()->findLessonsByIds($lessonIds);
        $lessons = ArrayToolkit::index($lessons, 'id');
        $courseIds =  ArrayToolkit::column($schedules?:array(), 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');
        $scheduleGroup = ArrayToolkit::group($schedules?:array(), 'date');
        if($sunday) {
            $default = array();
            for ($i=0; $i < 7; $i++) { 
                $day = date('Ymd',strtotime('+' . $i . 'days',strtotime($sunday)));
                $default[$day] = '';
            }
            $scheduleGroup = $scheduleGroup + $default;
        }
        ksort($scheduleGroup, SORT_NUMERIC);
        $result['schedules'] = $scheduleGroup;
        $result['courses'] = $courses;
        $result['lessons'] = $lessons;
        return $result;
    }

    private function getScheduleDao()
    {
        return $this->createDao('Schedule.ScheduleDao');
    }

    private function getCourseService()
    {
        return $this->createService("Course.CourseService");
    }
}