<?php

namespace Custom\WebBundle\Extensions\DataTag;

use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Extensions\DataTag\DataTag;
use Topxia\WebBundle\Extensions\DataTag\BaseDataTag;

//use Topxia\WebBundle\Extensions\DataTag\CourseBaseDataTag;

class LiveCoursesDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取最新课程列表
     *
     * @todo  一个课程下有２个直播课时的话，会返回２个相同的课程
     *
     * 可传入的参数：
     *   categoryId   可选 分类ID
     *   categoryCode 可选　分类CODE
     *   free         可选 免费课程
     *   count        必需 课程数量，取值不能超过100
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
        $recentlessons = $this->getCourseService()->findRecentLiveLesson($arguments['count']);

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

                if (isset($recentCourses[$lesson['courseId']])) {
                    continue;
                }

                $course             = $courses[$lesson['courseId']];
                $course['lesson']   = $lesson;
                $course['teachers'] = $this->getUserService()->findUsersByIds($course['teacherIds']);

                if ($i >= $arguments['count']) {
                    break;
                }

                $recentCourses[$lesson['courseId']] = $course;
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

        if (isset($arguments['free'])) {
            $coinSetting = $this->getSettingService()->get("coin");
            $coinEnable  = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1;
            $priceType   = "RMB";

            if ($coinEnable && !empty($coinSetting) && array_key_exists("price_type", $coinSetting)) {
                $priceType = $coinSetting["price_type"];
            }

            if ($arguments['free']) {
                if ($priceType == 'RMB') {
                    $conditions['price'] = '0.00';
                } else {
                    $conditions['coinPrice'] = '0.00';
                }
            } else {
                if ($priceType == 'RMB') {
                    $conditions['price_GT'] = '0.00';
                } else {
                    $conditions['coinPrice_GT'] = '0.00';
                }
            }
        }

        return $conditions;
    }

    protected function getCourseTeachersAndCategories($courses)
    {
        $userIds     = array();
        $categoryIds = array();

        foreach ($courses as $course) {
            $userIds       = array_merge($userIds, $course['teacherIds']);
            $categoryIds[] = $course['categoryId'];
        }

        $users    = $this->getUserService()->findUsersByIds($userIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        foreach ($users as $key => $user) {
            if ($user['id'] == $profiles[$user['id']]['id']) {
                $users[$key]['profile'] = $profiles[$user['id']];
            }
        }

        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        foreach ($courses as &$course) {
            $teachers = array();

            foreach ($course['teacherIds'] as $teacherId) {
                $user = $users[$teacherId];
                unset($user['password']);
                unset($user['salt']);
                $teachers[] = $user;
            }

            $course['teachers'] = $teachers;

            $categoryId = $course['categoryId'];

            if ($categoryId != 0 && array_key_exists($categoryId, $categories)) {
                $course['category'] = $categories[$categoryId];
            }
        }

        return $courses;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}
