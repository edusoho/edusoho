<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ArticleCategoryController extends BaseController
{
    public function embedAction($layout)
    {
        $categories = $this->getCategoryService()->findAllCategories();
        return $this->render('TopxiaAdminBundle:Article_Category:embed.html.twig', array(
            'categories' => $categories,
            'layout' => $layout
        ));
    }

    public function createAction(Request $request)
    {
        
        if ($request->getMethod() == 'POST') {
            $category = $this->getCategoryService()->createCategory($request->request->all());
            return $this->renderTbody();
        }

        $category = array(
            'id' => 0,
            'name' => '',
            'code' => '',
            'pagesize' => '10',
            // 'groupId' => (int) $request->query->get('groupId'),
            'parentId' => (int) $request->query->get('parentId', 0),
            'weight' => 0,
            'publishArticle' => 1
        );

        return $this->render('TopxiaAdminBundle:Article_Category:modal.html.twig', array(
            'category' => $category
        ));
    }

    public function editAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);
        if (empty($category)) {
            throw $this->createNotFoundException();
        }

        if ($request->getMethod() == 'POST') {
            $category = $this->getCategoryService()->updateCategory($id, $request->request->all());
            return $this->renderTbody();
        }

        return $this->render('TopxiaAdminBundle:Category:modal.html.twig', array(
            'category' => $category,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);
        if (empty($category)) {
            throw $this->createNotFoundException();
        }

        $this->getCategoryService()->deleteCategory($id);

        return $this->renderTbody($category['groupId']);
    }

    public function checkCodeAction(Request $request)
    {
        $code = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $avaliable = $this->getCategoryService()->isCategoryCodeAvaliable($code, $exclude);

        if ($avaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '编码已被占用，请换一个。');
        }

        return $this->createJsonResponse($response);
    }

    private function renderTbody()
    {
        $categories = $this->getCategoryService()->findAllCategories();
        return $this->render('TopxiaAdminBundle:Article_Category:tbody.html.twig', array(
            'categories' => $categories
        ));
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article.CategoryService');
    }
}