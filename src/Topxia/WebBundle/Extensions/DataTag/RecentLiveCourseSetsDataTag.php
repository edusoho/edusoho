<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;

class RecentLiveCourseSetsDataTag extends CourseBaseDataTag implements DataTag
{

    /**
     * 获取最新课程列表
     *
     * @todo  一个课程下有２个直播课时的话，会返回２个相同的课程
     *
     * 可传入的参数：
     *   count    必需 课程数量，取值不能超过100
     *
     * @param  array $arguments 参数
     *
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
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

        $recentTasksCondition = array(
            'status'     => 'published',
            'endTime_GT' => time(),
            'type'       => 'live'
        );

        $recentTasks = $this->getTaskService()->search(
            $recentTasksCondition,
            array('startTime' => 'ASC'),
            0,
            1000
        );

        $courses       = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($recentTasks, 'courseId'));
        $courses       = ArrayToolkit::index($courses, 'id');
        $recentCourses = array();

        foreach ($recentTasks as $task) {
            $course = $courses[$task['courseId']];
            if ($course['status'] != 'published') {
                continue;
            }
            if ($course['parentId'] != 0) {
                continue;
            }
            $course['task']     = $task;
            $course['teachers'] = $this->getUserService()->findUsersByIds($course['teacherIds']);

            if (count($recentCourses) >= $count) {
                break;
            }

            $recentCourses[] = $course;
        }

        return $this->getCourseTeachersAndCategories($recentCourses);
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

}
