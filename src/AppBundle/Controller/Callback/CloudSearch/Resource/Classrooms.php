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
        $classrooms = $this->buildTags($classrooms);

        return $classrooms;
    }

    protected function buildTags($classrooms)
    {
        $tagRelationsGroupByClassroomId = $this->getTagService()->findGroupTagIdsByOwnerTypeAndOwnerIds('classroom', array_column($classrooms, 'id'));

        $tagIds = array_reduce($tagRelationsGroupByClassroomId, function ($carry, $tagIds) {
            return array_merge($carry, $tagIds);
        }, array());
        $tagIds = array_unique($tagIds);
        $tagIds = array_values($tagIds);
        $tags = $this->getTagService()->findTagsByIds($tagIds);
        foreach ($classrooms as &$classroom) {
            $classroom['tags'] = array();
            if (!empty($tagRelationsGroupByClassroomId[$classroom['id']])) {
                $classroomCorreTagIds = $tagRelationsGroupByClassroomId[$classroom['id']];
                foreach ($classroomCorreTagIds as $tagId) {
                    if (!empty($tags[$tagId])) {
                        $tag = $tags[$tagId];
                        $classroom['tags'][] = $tag['name'];
                    }
                }
            }
        }

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

    /**
     * @return Biz\Taxonomy\Service\TagService
     */
    protected function getTagService()
    {
        return $this->getBiz()->service('Taxonomy:TagService');
    }
}
