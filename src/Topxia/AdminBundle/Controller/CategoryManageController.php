<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CategoryManageController extends BaseController
{
    public function indexAction(Request $request, $categoryId)
    {
        return $this->forward('TopxiaAdminBundle:CategoryManage:base',  array('categoryId' => $categoryId));
    }

    public function baseAction(Request $request, $categoryId)
    {
        $category = $this->getCategoryService()->getCategory($categoryId);
        if($request->getMethod() == 'POST'){
            $fields = $request->request->all();
            $category = $this->getCategoryService()->updateCategory($categoryId, $fields);
            return $this->render('TopxiaAdminBundle:CategoryManage:base.html.twig', array(
                'category' => $category,
            ));
        }

        return $this->render('TopxiaAdminBundle:CategoryManage:base.html.twig', array(
            'category' => $category,
        ));
    }

    public function editAction(Request $request, $categoryId)
    {
        $category = $this->getCategoryService()->getCategory($categoryId);
        if (empty($category)) {
            throw $this->createNotFoundException();
        }

        $fields = $request->request->all();
        $fields = ArrayToolkit::parts($fields, array('description','name', 'code', 'weight'));
        $category = $this->getCategoryService()->updateCategory($categoryId, $fields);
        return $this->createJsonResponse(true);
        
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getKnowledgeService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.KnowledgeService');
    }

}