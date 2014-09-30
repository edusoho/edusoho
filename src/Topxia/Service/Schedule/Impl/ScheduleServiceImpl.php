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

    public function saveSchedules($schedules) 
    {
        if(empty($schedules)) {
            return;
        }
        $this->getScheduleDao()->getConnection()->beginTransaction();
        try{
            $this->deleteOneDaySchedules($schedules[0]['classId'], $schedules[0]['date']);
            foreach ($schedules as $schedule) {
                $this->addSchedule($schedule);
            }
            $this->getScheduleDao()->getConnection()->commit();
        }catch(\Exception $e){
            $this->getScheduleDao()->getConnection()->rollback();
            throw $e;
        }
    }

    public function deleteOneDaySchedules($classId, $day)
    {
        return $this->getScheduleDao()->deleteOneDaySchedules($classId, $day);
    }

    public function findScheduleLessonsByWeek($classId, $sunday)
    {
        $week = date('w');
        $day = date('Ymd', strtotime('- {$week} days'));
        $sunday = $sunday ? : date('Ymd', strtotime("- {$week} days"));
        $staturday = date('Ymd', strtotime('+ 6 days', strtotime($sunday)));
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
        $courseIds =  ArrayToolkit::column($lessons?:array(), 'courseId');
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
        $months = array_keys($scheduleGroup);
        $i = 1;
        $changeMonth = true;
        while ($i <= 6) {
            if(substr($months[$i-1],4,2) != substr($months[$i],4,2)) {
                $changeMonth = false;
                break;
            }
            $i++;
        }
        $result['schedules'] = $scheduleGroup;
        $result['courses'] = $courses;
        $result['lessons'] = $lessons;
        $result['changeMonth'] = $changeMonth;
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