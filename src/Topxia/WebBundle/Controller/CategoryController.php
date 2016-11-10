<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class CategoryController extends BaseController
{
    public function allAction()
    {
        $categories = $this->getCategoryService()->findCategories(1);

        $data = array();
        foreach ($categories as $category) {
            $data[$category['id']] = array($category['name'], $category['parentId']);
        }

        return $this->createJsonResponse($data);
    }


    //$category, $path, $filter = array('price'=>'all','type'=>'all', 'currentLevelId'=>'all'), $orderBy = 'latest'
    public function treeNavAction(Request $request, $tagGroups, $tags, $categories, $category, $path, $filter = array('price'=>'all','type'=>'all', 'currentLevelId'=>'all'), $orderBy = 'latest')
    {
        // list($rootCategories, $categories, $activeIds) = $this->getCategoryService()->makeNavCategories($category, 'course');

        return $this->render("TopxiaWebBundle:Category:explore-nav.html.twig", array(
            // 'rootCategories' => $rootCategories,
            // 'categories'     => $categories,
            // 'category'       => $category,
            // 'activeIds'      => $activeIds,
            'categories'     => $categories,
            'path'           => $path,
            'filter'         => $filter,
            'orderBy'        => $orderBy,
            'categories'     => $categories,
            'tagGroups'      => $tagGroups,
            'tags'           => $tags
        ));
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
