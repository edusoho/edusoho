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

    public function saveSchedules($classId, $schedules, $date) 
    {
        $this->getScheduleDao()->getConnection()->beginTransaction();
        try{
            $this->deleteOneDaySchedules($classId, $date);
            if(!empty($schedules)) {
                foreach ($schedules as $schedule) {
                    $this->addSchedule($schedule);
                }
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
        
        return $this->makeUpResultForWeek($schedules, $sunday);
    }

    public function findScheduleLessonsByMonth($classId, $period)
    {
        asort($period,SORT_NUMERIC);
        $startDay = current($period);
        $endDay = end($period);
        $schedules = $this->getScheduleDao()->findScheduleByPeriod($classId, $startDay, $endDay);
        return $this->makeUpResultForMonth($schedules);
    }

    private function makeUpResultForMonth($schedules)
    {
        $lessonIds = ArrayToolkit::column($schedules?:array(), 'lessonId');
        $lessons = $this->getCourseService()->findLessonsByIds($lessonIds);
        $lessons = ArrayToolkit::index($lessons, 'id');
        $courseIds =  ArrayToolkit::column($lessons?:array(), 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');
        $teacherIds = ArrayToolkit::column($courses, 'teacherIds');
        $teacherIds_merged = array();
        foreach ($teacherIds as $item) {
            $teacherIds_merged = array_merge($teacherIds_merged, $item);
        }
        $teachers = $this->getUserSerivce()->findUsersByIds($teacherIds_merged);
        $scheduleGroup = ArrayToolkit::group($schedules?:array(), 'date');
     
        $result['schedules'] = $scheduleGroup;
        $result['courses'] = $courses;
        $result['lessons'] = $lessons;
        $result['teachers'] = $teachers;
        return $result;
    }

    private function makeUpResultForWeek($schedules, $sunday)
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

    public function findOneDaySchedules($classId, $date)
    {
        $schedules = $this->getScheduleDao()->findScheduleByPeriod($classId, $date, $date);
        return $this->makeUpResultOneDay($schedules);
    }

    private function makeUpResultOneDay($schedules)
    {
        $lessonIds = ArrayToolkit::column($schedules?:array(), 'lessonId');
        $lessons = $this->getCourseService()->findLessonsByIds($lessonIds);
        $lessons = ArrayToolkit::index($lessons, 'id');
        $courseIds =  ArrayToolkit::column($lessons?:array(), 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');
        $teacherIds = ArrayToolkit::column($courses, 'teacherIds');
        $teacherIds_merged = array();
        foreach ($teacherIds as $item) {
            $teacherIds_merged = array_merge($teacherIds_merged, $item);
        }
        $teachers = $this->getUserSerivce()->findUsersByIds($teacherIds_merged);
        $schedules = ArrayToolkit::index($schedules?:array(), 'lessonId');

        $result['schedules'] = $schedules;
        $result['courses'] = $courses;
        $result['lessons'] = $lessons;
        $result['teachers'] = $teachers;
        return $result;
    }

    public function findOneDaySchedulesByUserId($classId, $userId, $date)
    {
        if($classId == 0) {
            $schedules = $this->getScheduleDao()->findScheduleByPeriod2($date, $date);
        } else {
            $schedules = $this->getScheduleDao()->findScheduleByPeriod($classId, $date, $date);
        }
        return $this->makeUpResultOneDayByUserId($schedules, $userId);
    }

    private function makeUpResultOneDayByUserId($schedules, $userId)
    {
        
        $lessonIds = ArrayToolkit::column($schedules?:array(), 'lessonId');
        $lessons = $this->getCourseService()->findLessonsByIds($lessonIds);
        $lessons = ArrayToolkit::index($lessons, 'id');
        $courseIds =  ArrayToolkit::column($lessons?:array(), 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');

        foreach ($courses as $id => $course) {
            if(!in_array($userId, $course['teacherIds'])) {
                unset($courses[$id]);
            }
        }

        foreach ($lessons as $key => $lesson) {
            if(!in_array($lesson['courseId'], array_keys($courses))) {
                unset($lessons[$key]);
            }
        }

        $schedules = ArrayToolkit::index($schedules?:array(), 'lessonId');
        foreach ($schedules as $key => $schedule) {
            if(!in_array($key, array_keys($lessons))) {
                unset($schedules[$key]);
            }
        }

        $classIds = ArrayToolkit::column($schedules?:array(), 'classId');
        $classes = $this->getClassesService()->findClassesByIds($classIds);
        $classes = ArrayToolkit::index($classes?:array(), 'id');

        $result['courses'] = $courses;
        $result['lessons'] = $lessons;
        $result['schedules'] = $schedules;
        $result['classes'] = $classes;
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

    protected function getClassesService()
    {
        return $this->createService('Classes.ClassesService');
    }

    private function getUserSerivce()
    {
        return $this->createService('User.UserService');
    }
}