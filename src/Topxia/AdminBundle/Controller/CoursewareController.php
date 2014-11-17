<?php 
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class CoursewareController extends BaseController
{
    public function manageAction(Request $request, $categoryId)
    {
    	   $category = $this->getCategoryService()->getCategory($categoryId);
        return $this->render('TopxiaAdminBundle:Courseware:manage.html.twig',array(
            'category' => $category
        ));
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}