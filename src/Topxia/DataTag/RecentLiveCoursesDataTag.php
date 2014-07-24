<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

class RecentLiveCoursesDataTag extends CourseBaseDataTag implements DataTag  
{

    /**
     * 获取最新课程列表
     *
     * 可传入的参数：
     *   count    必需 课程数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {	
        $conditions = array('status' => 'published', 'type' => 'normal');

        $courseSetting = $this->getSettingService()->get('course', array());

        if (!empty($courseSetting['live_course_enabled']) && $courseSetting['live_course_enabled']) {
            $recentLiveCourses = $this->getRecentLiveCourses($arguments['count']);
        } else {
            $recentLiveCourses = array();
        }

        return $recentLiveCourses;

    }

    private function getRecentLiveCourses($count)
    {

        $recenntLessonsCondition = array(
            'status' => 'published',
            'endTimeGreaterThan' => time(),
        );

        $recentlessons = $this->getCourseService()->searchLessons(
            $recenntLessonsCondition,  
            array('startTime', 'ASC'),
            0,
            $count
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($recentlessons, 'courseId'));

        $recentCourses = array();
        foreach ($recentlessons as $lesson) {
            $course = $courses[$lesson['courseId']];
            if ($course['status'] != 'published') {
                continue;
            }
            $course['lesson'] = $lesson;
            $course['teachers'] = $this->getUserService()->findUsersByIds($course['teacherIds']);

            if (count($recentCourses) >= 8) {
                break;
            }

            $recentCourses[] = $course;
        }

        return $this->getCourseTeachersAndCategories($recentCourses);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}
