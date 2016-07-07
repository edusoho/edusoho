<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

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
            $courses = $this->getCourseService()->searchCourses($conditions, array('updatedTime', 'ASC'), $start, $limit);
            $courses = $this->assemblyCourses($courses);
            $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $courses);
            return $this->wrap($this->filter($courses), $next);
        } else {
            $total = $this->getCourseService()->searchCourseCount($conditions);
            $courses = $this->getCourseService()->searchCourses($conditions, array('createdTime','DESC'), $start, $limit);
            return $this->wrap($this->filter($courses), $total);
        }

    }

    public function discoveryColumn(Application $app, Request $request)
    {
        $result = $request->query->all();
        $conditions['categoryId'] = $result['categoryId'];
        if ($conditions['categoryId'] == 0) {
            unset($conditions['categoryId']);
        }
        if ($result['orderType'] == 'hot') {
            $orderBy = 'studentNum';
        }
        elseif ($result['orderType'] == 'recommend') {
            $orderBy = 'recommendedSeq';
            $conditions['recommended'] = 1;
        }
        else {
            $orderBy = 'createdTime';
        }

        if ($result['type'] == 'live') {
            $conditions['type'] = 'live';
        }
        else {
            $conditions['type'] = 'normal';
        }
        if (empty($result['showCount'])) {
            $result['showCount'] = 6;
        }
        
        $conditions['parentId'] = 0;
        $conditions['status'] = 'published';

        
        $courses = $this->getCourseService()->searchCourses($conditions,$orderBy,0,$result['showCount']);
        if ($result['orderType'] == 'recommend' && count($courses)<$result['showCount']) {
            $conditions['recommended'] = 0;
            $unrecommendCourses = $this->getCourseService()->searchCourses($conditions,'createdTime',0,$result['showCount']-count($courses));
            $courses = array_merge($courses, $unrecommendCourses);
        }
        $courses = $this->filter($courses);
        foreach ($courses as $key => $value) {
            $courses[$key]['createdTime'] = strval(strtotime($value['createdTime']));
            $courses[$key]['updatedTime'] = strval(strtotime($value['updatedTime']));
            $userIds = $courses[$key]['teacherIds'];
            $courses[$key]['teachers'] = $this->getUserService()->findUsersByIds($userIds);
            $courses[$key]['teachers'] = array_values($this->multicallFilter('User', $courses[$key]['teachers']));
        }   
        $total = count($courses);
        return $this->wrap($courses, min($result['showCount'], $total));
    }
    public function post(Application $app, Request $request)
    {
        
    }

    protected function assemblyCourses(&$courses)
    {
        $tagIds = array();
        foreach ($courses as $course) {
            $tagIds = array_merge($tagIds, $course['tags']);
        }

        $tags = $this->getTagService()->findTagsByIds($tagIds);

        $categoryIds = ArrayToolkit::column($courses, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        foreach ($courses as &$course) {
            $courseTags = array();
            if (empty($course['tags'])) {
                continue;
            }
            foreach ($course['tags'] as $tagId) {
                if (empty($tags[$tagId])) {
                    continue;
                } 
                $courseTags[] = array(
                    'id' => $tagId,
                    'name' => $tags[$tagId]['name'],
                );
            }
            $course['tags'] = $courseTags;
        }

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

    public function filter(&$res)
    {
        return $this->multicallFilter('Course', $res);
    }

    protected function multicallFilter($name, &$res)
    {
        foreach ($res as &$one) {
            $this->callFilter($name, $one);
        }
        return $res;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
