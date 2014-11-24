<?php 
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class ArticleMaterialController extends BaseController
{
    public function manageAction(Request $request, $categoryId)
    {
        $category = $this->getCategoryService()->getCategory($categoryId);
        return $this->render('TopxiaAdminBundle:ArticleMaterial:manage.html.twig',array(
            'category' => $category
        ));
    }

    public function createAction(Request $request, $categoryId)
    {
        $category = $this->getCategoryService()->getCategory($categoryId);
        return $this->render('TopxiaAdminBundle:ArticleMaterial:modal.html.twig',array(
            'category' => $category
        ));
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}