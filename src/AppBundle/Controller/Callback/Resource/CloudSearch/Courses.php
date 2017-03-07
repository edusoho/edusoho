<?php

namespace AppBundle\Controller\Callback\Resource\CloudSearch;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Callback\Resource\BaseResource;

class Courses extends BaseResource
{
    public function get(Request $request)
    {
        $conditions = $request->query->all();

        $cursor = $request->query->get('cursor', time());
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);
        
        $conditions['status']         = 'published';
        $conditions['parentId']       = 0;
        $conditions['updatedTime_GE'] = $cursor;
        $courses                      = $this->getCourseSetService()->searchCourseSets($conditions, array('updatedTime' => 'ASC'), $start, $limit);
        $courses                      = $this->assemblyCourses($courses);
        $next                         = $this->nextCursorPaging($cursor, $start, $limit, $courses);

        return $this->wrap($this->filter($courses), $next);
    }

    protected function assemblyCourses($courses)
    {
        $categoryIds = ArrayToolkit::column($courses, 'categoryId');
        $categories  = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        foreach ($courses as &$course) {
            if (isset($categories[$course['categoryId']])) {
                $course['category'] = array(
                    'id'   => $categories[$course['categoryId']]['id'],
                    'name' => $categories[$course['categoryId']]['name']
                );
            } else {
                $course['category'] = array();
            }
        }

        return $courses;
    }

    public function filter($res)
    {
        return $this->multicallFilter('cloud_search_course', $res);
    }
    
    /**
     * @return Biz\Course\Service\CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }
    
    /**
     * @return Biz\Taxonomy\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->getBiz()->service('Taxonomy:CategoryService');
    }
    
    /**
     * @return Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}