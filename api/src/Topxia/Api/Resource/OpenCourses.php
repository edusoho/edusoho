<?php

namespace Topxia\Api\Resource;

use Biz\OpenCourse\Service\OpenCourseService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\Taxonomy\Service\TagService;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class OpenCourses extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = $request->query->all();

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);

        $total = $this->getOpenCourseService()->countCourses($conditions);
        $openCourses = $this->getOpenCourseService()->searchCourses($conditions, array('createdTime' => 'DESC'), $start, $limit);
        $openCourses = $this->assembly($openCourses);

        return $this->wrap($this->filter($openCourses), $total);
    }

    protected function assembly(array $openCourses)
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

    public function filter($res)
    {
        return $this->multicallFilter('OpenCourse', $res);
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse:OpenCourseService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:TagService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }
}
