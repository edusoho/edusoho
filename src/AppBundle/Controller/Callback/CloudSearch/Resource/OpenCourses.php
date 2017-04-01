<?php

namespace AppBundle\Controller\Callback\CloudSearch\Resource;

use AppBundle\Controller\Callback\CloudSearch\BaseProvider;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class OpenCourses extends BaseProvider
{
    public function get(Request $request)
    {
        $conditions = $request->query->all();

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);

        $conditions['status'] = 'published';
        $conditions['parentId'] = 0;
        $conditions['updatedTime_GE'] = $conditions['cursor'];
        $openCourses = $this->getOpenCourseService()->searchCourses($conditions, array('createdTime' => 'ASC'), $start, $limit);
        $openCourses = $this->build($openCourses);
        $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $openCourses);

        return $this->wrap($this->filter($openCourses), $next);
    }

    public function build($openCourses)
    {
        $openCourses = $this->buildCategories($openCourses);
        $openCourses = $this->buildTags($openCourses);

        return $openCourses;
    }

    protected function buildCategories($openCourses)
    {
        $categoryIds = ArrayToolkit::column($openCourses, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        foreach ($openCourses as &$course) {
            if (isset($categories[$course['categoryId']])) {
                $course['category'] = array(
                    'id' => $categories[$course['categoryId']]['id'],
                    'name' => $categories[$course['categoryId']]['name'],
                );
            } else {
                $course['category'] = array();
            }
        }

        return $openCourses;
    }

    protected function buildTags($openCourses)
    {
        $tagIdGroups = ArrayToolkit::column($openCourses, 'tags');
        $tagIds = ArrayToolkit::mergeArraysValue($tagIdGroups);

        $tags = $this->getTagService()->findTagsByIds($tagIds);

        foreach ($openCourses as &$openCourse) {
            $openCourseTagIds = $openCourse['tags'];
            $openCourse['tags'] = array();
            if (!empty($openCourseTagIds)) {
                foreach ($openCourseTagIds as $index => $openCourseTagId) {
                    if (isset($tags[$openCourseTagId])) {
                        $openCourse['tags'][$index] = array(
                            'id' => $tags[$openCourseTagId]['id'],
                            'name' => $tags[$openCourseTagId]['name'],
                        );
                    }
                }
            }
        }

        return $openCourses;
    }

    public function filter($res)
    {
        return $this->multicallFilter('open_course', $res);
    }

    /**
     * @return Biz\OpenCourse\Service\OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->getBiz()->service('OpenCourse:OpenCourseService');
    }

    /**
     * @return Biz\Taxonomy\Service\TagService
     */
    protected function getTagService()
    {
        return $this->getBiz()->service('Taxonomy:TagService');
    }

    /**
     * @return Biz\Taxonomy\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->getBiz()->service('Taxonomy:CategoryService');
    }
}
