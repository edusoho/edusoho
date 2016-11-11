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


    public function treeNavAction(Request $request, $tagGroups, $tags, $categories, $category, $path, $filter = array('price'=>'all','type'=>'all', 'currentLevelId'=>'all'), $orderBy = 'latest')
    {
        // var_dump($subCategories);
        // var_dump($subCategory);exit();
        return $this->render("TopxiaWebBundle:Category:explore-nav.html.twig", array(
            'categories'        => $categories,
            'selectedCategory'  => $category,
            // 'subCategories'     => $subCategories,
            // 'subCategory'       => $subCategory,
            'path'              => $path,
            'filter'            => $filter,
            'orderBy'           => $orderBy,
            'tagGroups'         => $tagGroups,
            'tags'              => $tags
        ));
    }
}
