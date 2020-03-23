<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TreeToolkit;
use AppBundle\Controller\BaseController;
use Biz\Question\Service\CategoryService;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Symfony\Component\HttpFoundation\Request;

class QuestionCategoryController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        $categories = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['id']);

        return $this->render('question-bank/question-category/index.html.twig', array(
            'questionBank' => $questionBank,
            'categories' => $this->getItemCategoryService()->getItemCategoryTree($questionBank['id']),
            'users' => $this->getUserService()->findUsersByIds(ArrayToolkit::column($categories, 'updated_user_id')),
        ));
    }

    public function batchCreateAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $categoryNames = $request->request->get('categoryNames');
            $parentId = $request->request->get('parentId');
            $categoryNames = trim($categoryNames);
            $categoryNames = explode("\r\n", $categoryNames);
            $categoryNames = array_filter($categoryNames);

            $this->getItemCategoryService()->createItemCategories($id, $parentId, $categoryNames);

            return $this->createJsonResponse(array('success' => true, 'parentId' => $parentId));
        }

        $parentId = $request->query->get('parentId', 0);

        return $this->render('question-bank/question-category/batch-create-modal.html.twig', array(
            'parentId' => $parentId,
            'bankId' => $id,
        ));
    }

    public function editAction(Request $request, $id)
    {
        if ('POST' == $request->getMethod()) {
            $name = $request->request->get('name', '');

            $this->getQuestionCategoryService()->updateCategory($id, array('name' => $name));

            return $this->createJsonResponse(array('success' => true));
        }

        $category = $this->getQuestionCategoryService()->getCategory($id);

        return $this->render('question-bank/question-category/update-modal.html.twig', array(
            'category' => $category,
        ));
    }

    public function getQuestionCountAction(Request $request, $id)
    {
        $children = $this->getQuestionCategoryService()->findCategoryChildrenIds($id);
        $children[] = $id;
        $questionCount = $this->getQuestionService()->searchCount(array('categoryIds' => $children));

        return $this->createJsonResponse(array('questionCount' => $questionCount));
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getQuestionCategoryService()->deleteCategory($id);

        return $this->createJsonResponse(array('success' => true));
    }

    public function showCategoriesAction(Request $request)
    {
        $bankId = $request->request->get('bankId', 0);
        $isTree = $request->request->get('isTree', false);
        if (!$this->getQuestionBankService()->canManageBank($bankId)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        if ($isTree) {
            $categories = $this->getQuestionCategoryService()->getCategoryStructureTree($bankId);
        } else {
            $categories = $this->getQuestionCategoryService()->getCategoryTree($bankId);
        }

        return $this->createJsonResponse($categories);
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return CategoryService
     */
    protected function getQuestionCategoryService()
    {
        return $this->createService('Question:CategoryService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->createService('ItemBank:Item:ItemCategoryService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }
}
