<?php

namespace Custom\WebBundle\Extensions\DataTag;

use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Extensions\DataTag\DataTag;
use Topxia\WebBundle\Extensions\DataTag\CourseBaseDataTag;

class LiveCoursesDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取最新课程列表
     *
     * @todo  一个课程下有２个直播课时的话，会返回２个相同的课程
     *
     * 可传入的参数：
     *   categoryId 可选 分类ID
     *   categoryCode 可选　分类CODE
     *   count    必需 课程数量，取值不能超过100
     *
     * @param  array $arguments      参数
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        $conditions = array('status' => 'published', 'type' => 'normal');

        $courseSetting = $this->getSettingService()->get('course', array());

        if (!empty($courseSetting['live_course_enabled']) && $courseSetting['live_course_enabled']) {
            $recentLiveCourses = $this->getRecentLiveCourses($arguments);
        } else {
            $recentLiveCourses = array();
        }

        return $recentLiveCourses;
    }

    private function getRecentLiveCourses($arguments)
    {
        $recenntLessonsCondition = array(
            'status' => 'published',
            'type'   => 'live'
        );
        $recentlessons = $this->getCourseService()->searchLessons(
            $recenntLessonsCondition,
            array('startTime', 'DESC'),
            0,
            1000
        );

        $conditions = array(
            'courseIds' => ArrayToolkit::column($recentlessons, 'courseId'),
            'parentId'  => 0,
            'status'    => 'published'
        );
        $conditions = array_merge($conditions, $this->_prepareCondition($arguments));

        $courses = $this->getCourseService()->searchCourses($conditions, array('createdTime', 'DESC'), 0, 100);
        $courses = ArrayToolkit::index($courses, 'id');

        $recentCourses = array();
        $i             = 0;

        if ($recentlessons) {
            foreach ($recentlessons as $lesson) {
                if (!isset($courses[$lesson['courseId']])) {
                    continue;
                }

                $course             = $courses[$lesson['courseId']];
                $course['lesson']   = $lesson;
                $course['teachers'] = $this->getUserService()->findUsersByIds($course['teacherIds']);

                if ($i >= $arguments['count']) {
                    break;
                }

                $recentCourses[] = $course;
                $i++;
            }
        }

        return $this->getCourseTeachersAndCategories($recentCourses);
    }

    private function _prepareCondition($arguments)
    {
        $conditions = array();

        if (!empty($arguments['categoryId'])) {
            $conditions['categoryId'] = $arguments['categoryId'];
        }

        if (!empty($arguments['categoryCode'])) {
            $category                 = $this->getCategoryService()->getCategoryByCode($arguments['categoryCode']);
            $conditions['categoryId'] = empty($category) ? -1 : $category['id'];
        }

        return $conditions;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
