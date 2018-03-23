<?php

namespace AppBundle\Controller\Callback\CloudSearch\Resource;

use AppBundle\Controller\Callback\CloudSearch\BaseProvider;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

/**
 * 班级资源集合(对应classroom表).
 */
class Classrooms extends BaseProvider
{
    public function get(Request $request)
    {
        $conditions = $request->query->all();
        $cursor = $request->query->get('cursor', time());
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);

        $conditions['status'] = 'published';
        $conditions['parentId'] = 0;
        $conditions['updatedTime_GE'] = $cursor;

        $classrooms = $this->getClassroomService()->searchClassrooms($conditions, array('updatedTime' => 'ASC'), $start, $limit);
        $classrooms = $this->build($classrooms);
        $next = $this->nextCursorPaging($cursor, $start, $limit, $classrooms);

        return $this->wrap($this->filter($classrooms), $next);
    }

    public function filter($res)
    {
        return $this->multicallFilter('classroom', $res);
    }

    public function build($classrooms)
    {
        $classrooms = $this->buildCategories($classrooms);

        return $classrooms;
    }

    protected function buildCategories($classrooms)
    {
        $categoryIds = ArrayToolkit::column($classrooms, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        foreach ($classrooms as &$classroom) {
            if (isset($categories[$classroom['categoryId']])) {
                $classroom['category'] = array(
                    'id' => $categories[$classroom['categoryId']]['id'],
                    'name' => $categories[$classroom['categoryId']]['name'],
                );
            } else {
                $classroom['category'] = array();
            }
        }

        return $classrooms;
    }

    /**
     * @return \Biz\Classroom\Service\ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return \Biz\Taxonomy\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
