<?php

namespace AppBundle\Controller\Admin;

use Biz\Taxonomy\CategoryException;
use Symfony\Component\HttpFoundation\Request;

class QuestionBankCategoryController extends BaseController
{
    public function indexAction(Request $request)
    {
        $categories = $this->getCategoryService()->getCategoryStructureTree();

        return $this->render('admin/question-bank-category/index.html.twig', array(
            'categories' => $categories,
            'selectOrg' => $request->query->get('orgCode', ''),
        ));
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $category = $this->getCategoryService()->createCategory($request->request->all());

            return $this->createJsonResponse($category);
        }

        $category = array(
            'id' => 0,
            'name' => '',
            'parentId' => (int) $request->query->get('parentId', 0),
        );

        return $this->render('admin/question-bank-category/modal.html.twig', array(
            'category' => $category,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);

        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        if ('POST' == $request->getMethod()) {
            $category = $this->getCategoryService()->updateCategory($id, $request->request->all());

            return $this->createJsonResponse($category);
        }

        return $this->render('admin/question-bank-category/modal.html.twig', array(
            'category' => $category,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);

        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        $this->getCategoryService()->deleteCategory($id);

        return $this->createJsonResponse(true);
    }

    public function sortAction(Request $request)
    {
        $ids = $request->request->get('ids');

        if (!empty($ids)) {
            $this->getCategoryService()->sortCategories($ids);
        }

        return $this->createJsonResponse(true);
    }

    protected function getCategoryService()
    {
        return $this->createService('QuestionBank:CategoryService');
    }
}
