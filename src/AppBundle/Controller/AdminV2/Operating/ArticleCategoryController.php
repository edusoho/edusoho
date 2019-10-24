<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Article\CategoryException;
use Biz\Article\Service\ArticleService;
use Biz\Article\Service\CategoryService;
use Symfony\Component\HttpFoundation\Request;

class ArticleCategoryController extends BaseController
{
    public function indexAction(Request $request)
    {
        $categories = $this->getCategoryService()->getCategoryStructureTree();

        return $this->render('admin-v2/operating/article-category/index.html.twig', array(
            'categories' => $categories,
        ));
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $category = $this->getCategoryService()->createCategory($request->request->all());

            return $this->renderTbody();
        }
        $category = array(
            'id' => 0,
            'name' => '',
            'code' => '',
            'parentId' => (int) $request->query->get('parentId', 0),
            'weight' => 0,
            'publishArticle' => 1,
            'seoTitle' => '',
            'seoKeyword' => '',
            'seoDesc' => '',
            'published' => 1,
        );

        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('admin-v2/operating/article-category/modal.html.twig', array(
            'category' => $category,
            'categoryTree' => $categoryTree,
        ));
    }

    public function sortAction(Request $request)
    {
        $ids = $request->request->get('ids');

        if (!empty($ids)) {
            $this->getCategoryService()->sortCategories($ids);
        }

        return $this->createJsonResponse(true);
    }

    public function editAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);
        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        if ('POST' == $request->getMethod()) {
            $this->getCategoryService()->updateCategory($id, $request->request->all());

            return $this->renderTbody();
        }
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('admin-v2/operating/article-category/modal.html.twig', array(
            'category' => $category,
            'categoryTree' => $categoryTree,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);
        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        if ($this->canDeleteCategory($id)) {
            return $this->createJsonResponse(array('status' => 'error', 'message' => '此栏目有子栏目，无法删除'));
        } else {
            $this->getCategoryService()->deleteCategory($id);

            return $this->createJsonResponse(array('status' => 'success', 'message' => '栏目已删除'));
        }
    }

    public function canDeleteCategory($id)
    {
        return $this->getCategoryService()->findCategoriesCountByParentId($id);
    }

    public function checkCodeAction(Request $request)
    {
        $code = $request->query->get('value');

        $exclude = $request->query->get('exclude');

        $available = $this->getCategoryService()->isCategoryCodeAvailable($code, $exclude);

        if ($available) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '编码已被占用，请换一个。');
        }

        return $this->createJsonResponse($response);
    }

    public function checkParentIdAction(Request $request)
    {
        $selectedParentId = $request->query->get('value');

        $currentId = $request->query->get('currentId');

        if ($currentId == $selectedParentId && 0 != $selectedParentId) {
            $response = array('success' => false, 'message' => '不能选择自己作为父栏目');
        } else {
            $response = array('success' => true, 'message' => '');
        }

        return $this->createJsonResponse($response);
    }

    protected function renderTbody()
    {
        $categories = $this->getCategoryService()->getCategoryTree();

        return $this->render('admin-v2/operating/article-category/tbody.html.twig', array(
            'categories' => $categories,
            'categoryTree' => $categories,
        ));
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Article:CategoryService');
    }

    /**
     * @return ArticleService
     */
    protected function getArticleService()
    {
        return $this->createService('Article:ArticleService');
    }
}
