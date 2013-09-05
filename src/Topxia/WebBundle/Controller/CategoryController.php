<?php
namespace Topxia\WebBundle\Controller;

class CategoryController extends BaseController
{

    public function showAction($code)
    {
        $categories = $this->getCategoryService()->findGroupRootCategories('course');

        return $this->render('TopxiaWebBundle:Category:show.html.twig', array(
            'categories' => $categories,
        ));
    }

    public function allAction()
    {
        $categories = $this->getCategoryService()->findCategories(1);

        $data = array();
        foreach ($categories as $category) {
            $data[$category['id']] = array($category['name'], $category['parentId']);
        }

        return $this->createJsonResponse($data);
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

}