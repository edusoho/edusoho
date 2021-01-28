<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\HttpFoundation\Request;

class Courses extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = $request->query->all();
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);
        $courses = $this->getCourseService()->searchCourses($conditions, array('createdTime' => 'DESC'), $start, $limit);
        $courses = $this->assemblyCourses($courses);
        $courses = $this->filter($courses);
        $next = $this->getCourseService()->searchCourseCount($conditions);

        return $this->wrap($courses, $next);
    }

    public function discoveryColumn(Application $app, Request $request)
    {
        $conditions = $request->query->all();
        list($orderBy, $count) = $this->getOrderByAndCountByConditions($conditions);
        $conditions = $this->filterConditions($conditions);

        $total = $this->getCourseService()->searchCourseCount($conditions);
        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            $orderBy,
            0,
            $count
        );

        $courses = $this->filter($courses);
        foreach ($courses as $key => $value) {
            $courses[$key]['createdTime'] = strval(strtotime($value['createdTime']));
            $courses[$key]['updatedTime'] = strval(strtotime($value['updatedTime']));
            $userIds = $courses[$key]['teacherIds'];
            $courses[$key]['teachers'] = $this->getUserService()->findUsersByIds($userIds);
            $courses[$key]['teachers'] = array_values($this->multicallFilter('User', $courses[$key]['teachers']));
        }

        return $this->wrap($courses, min($count, $total));
    }

    protected function getOrderByAndCountByConditions($conditions)
    {
        $count = empty($conditions['showCount']) ? 6 : $conditions['showCount'];
        $orderBy = array(
            'hot' => array(
                'hitNum' => 'DESC',
            ),
            'recommend' => array(
                'recommendedSeq' => 'ASC',
                'recommendedTime' => 'DESC',
            ),
        );

        if (!empty($conditions['orderType']) && !empty($orderBy[$conditions['orderType']])) {
            $orderBy = $orderBy[$conditions['orderType']];
        } else {
            $orderBy = array('createdTime' => 'DESC');
        }

        return array($orderBy, $count);
    }

    protected function filterConditions($conditions)
    {
        $conditions['status'] = 'published';
        $conditions['parentId'] = 0;
        if (!empty($conditions['type']) && 'live' != $conditions['type']) {
            $conditions['type'] = 'normal';
        }
        if (!empty($conditions['orderType']) && 'recommend' == $conditions['orderType']) {
            $conditions['recommended'] = 1;
        }
        $conditions = ArrayToolkit::parts($conditions, array(
            'categoryId',
            'type',
            'parentId',
            'status',
            'recommended',
        ));

        return $conditions;
    }

    public function filter($courses)
    {
        $courseIds = ArrayToolkit::column($courses, 'id');
        $courseSets = $this->getCourseSetService()->findCourseSetsByCourseIds($courseIds);

        $coursesFilter = array();
        foreach ($courses as $key => $course) {
            $courseSet = $courseSets[$course['courseSetId']];
            if ('published' == $courseSet['status']) {
                $course['hitNum'] = $courseSet['hitNum'];
                $course['courseSet'] = $courseSet;
                $coursesFilter[] = $course;
            }
        }

        return $this->multicallFilter('Course', $coursesFilter);
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

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
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
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
