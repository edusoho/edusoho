<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;

class RecentLiveCourseSetsDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取最新课程列表.
     *
     * @todo  一个课程下有２个直播课时的话，会返回２个相同的课程
     *
     * 可传入的参数：
     *   count    必需 课程数量，取值不能超过100
     *
     * @param array $arguments 参数
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
            'status' => 'published',
            'endTime_GT' => time(),
            'type' => 'live',
        );

        $recentTasks = $this->getTaskService()->searchTasks(
            $recentTasksCondition,
            array('startTime' => 'ASC'),
            0,
            1000
        );

        $courseSetIds = ArrayToolkit::column($recentTasks, 'fromCourseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
        $courseSets = ArrayToolkit::index($courseSets, 'id');
        $recentCourseSets = array();

        foreach ($recentTasks as $task) {
            if (empty($courseSets[$task['fromCourseSetId']])) {
                continue;
            }

            $courseSet = $courseSets[$task['fromCourseSetId']];
            if ('published' != $courseSet['status']) {
                continue;
            }

            if (0 != $courseSet['parentId']) {
                continue;
            }
            $courseSet['task'] = $task;

            if (count($recentCourseSets) >= $count) {
                break;
            }

            $recentCourseSets[] = $courseSet;
        }

        return $this->fillCourseSetTeachersAndCategoriesAttribute($recentCourseSets);
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }
}
