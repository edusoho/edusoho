<?php

namespace Topxia\Api\Resource;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseSetService;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class Courses extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = $request->query->all();

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);

        if (isset($conditions['cursor'])) {
            $conditions['status'] = 'published';
            $conditions['parentId'] = 0;
            $conditions['updatedTime_GE'] = $conditions['cursor'];
            $courses = $this->getCourseService()->searchCourses(
                $conditions,
                array('updatedTime' => 'ASC'),
                $start,
                $limit
            );
            $courses = $this->assemblyCourses($courses);
            $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $courses);

            return $this->wrap($this->filter($courses), $next);
        } else {
            $total = $this->getCourseService()->searchCourseCount($conditions);
            $courses = $this->getCourseService()->searchCourses(
                $conditions,
                array('createdTime' => 'DESC'),
                $start,
                $limit
            );
            $courses = $this->assemblyCourses($courses);

            return $this->wrap($this->filter($courses), $total);
        }
    }

    public function discoveryColumn(Application $app, Request $request)
    {
        $defaultQuery = array(
            'orderType' => '',
            'type' => '',
            'showCount' => '',
        );

        $result = array_merge($defaultQuery, $request->query->all());

        if (!empty($result['categoryId'])) {
            $conditions['categoryId'] = $result['categoryId'];
        }

        if ($result['orderType'] == 'hot') {
            $orderBy = 'hitNum';
        } elseif ($result['orderType'] == 'recommend') {
            $orderBy = 'recommendedSeq';
            $conditions['recommended'] = 1;
        } else {
            $orderBy = 'createdTime';
        }

        if ($result['type'] == 'live') {
            $conditions['type'] = 'live';
        } else {
            $conditions['type'] = 'normal';
        }
        if (empty($result['showCount'])) {
            $result['showCount'] = 6;
        }

        $conditions['status'] = 'published';
        $conditions['parentId'] = 0;

        $sets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            $orderBy,
            0,
            PHP_INT_MAX
        );

        $setIds = ArrayToolkit::column($sets, 'id');

        if (empty($setIds)) {
            return $this->wrap(array(), min($result['showCount'], 0));
        } else {
            $courseConditions = array(
                'courseSetIds' => $setIds,
            );

            $total = $this->getCourseService()->searchCourseCount($courseConditions);
            $courses = $this->getCourseService()->searchCourses(
                $courseConditions,
                array('createdTime' => 'DESC'),
                0,
                $result['showCount']
            );
            $courses = $this->filter($courses);
            foreach ($courses as $key => $value) {
                $courses[$key]['createdTime'] = strval(strtotime($value['createdTime']));
                $courses[$key]['updatedTime'] = strval(strtotime($value['updatedTime']));
                $userIds = $courses[$key]['teacherIds'];
                $courses[$key]['teachers'] = $this->getUserService()->findUsersByIds($userIds);
                $courses[$key]['teachers'] = array_values($this->multicallFilter('User', $courses[$key]['teachers']));
            }

            $this->_sortCoursesOrderBySetIds($setIds, $courses);

            return $this->wrap($courses, min($result['showCount'], $total));
        }
    }

    public function post(Application $app, Request $request)
    {
    }

    protected function assemblyCourses($courses)
    {
        $categoryIds = ArrayToolkit::column($courses, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        foreach ($courses as &$course) {
            if (isset($categories[$course['categoryId']])) {
                $course['category'] = array(
                    'id' => $categories[$course['categoryId']]['id'],
                    'name' => $categories[$course['categoryId']]['name'],
                );
            } else {
                $course['category'] = array();
            }
        }

        return $courses;
    }

    /**
     * 根据课程ID集合的顺序和教学计划所对应的课程, 排序教学计划, 注意请确保课程ID集合是已经排序好
     * @param $setIds
     * @param $courses
     */
    protected function _sortCoursesOrderBySetIds($setIds, &$courses)
    {
        if (empty($setIds)) {
            return;
        }

        /**
         * 翻转setId集合 如
         *  key 为序号， value 是 setId
         * [
         *   0 => 3,
         *   1 => 4,
         * ]
         * 会转换为
         * [
         *   3 => 0,
         *   4 => 1,
         * ]
         */
        $orderedSetIds = array_flip($setIds);
        var_dump($orderedSetIds);
        // 教学计划根据翻转后的setId集合来获得排序序号，然后根据序号来排序
        usort(
            $courses,
            function ($a, $b) use ($orderedSetIds) {
                $aSetSeq = $orderedSetIds[$a['courseSetId']];
                $bSetSeq = $orderedSetIds[$b['courseSetId']];

                if ($aSetSeq === $bSetSeq) {
                    return 0;
                }

                return ($a < $b) ? 1 : -1;  // 降序
            }
        );
    }

    public function filter($res)
    {
        return $this->multicallFilter('Course', $res);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
