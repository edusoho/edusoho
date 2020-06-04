<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Question\Service\CategoryService;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Symfony\Component\HttpFoundation\Request;

class QuestionCategoryController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        $categories = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['itemBankId']);

        return $this->render('question-bank/question-category/index.html.twig', [
            'questionBank' => $questionBank,
            'categories' => $this->getItemCategoryService()->getItemCategoryTree($questionBank['itemBankId']),
            'users' => $this->getUserService()->findUsersByIds(ArrayToolkit::column($categories, 'updated_user_id')),
        ]);
    }

    public function batchCreateAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $categoryNames = $request->request->get('categoryNames');
            $categoryNames = trim($categoryNames);
            $categoryNames = explode("\r\n", $categoryNames);
            $categoryNames = array_filter($categoryNames);
            $parentId = $request->request->get('parentId');
            $questionBank = $this->getQuestionBankService()->getQuestionBank($id);

            $this->getItemCategoryService()->createItemCategories($questionBank['itemBankId'], $parentId, $categoryNames);

            return $this->createJsonResponse(['success' => true, 'parentId' => $parentId]);
        }

        return $this->render('question-bank/question-category/batch-create-modal.html.twig', [
            'parentId' => $request->query->get('parentId', 0),
            'bankId' => $id,
        ]);
    }

    public function editAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name', '');

            $this->getItemCategoryService()->updateItemCategory($id, ['name' => $name]);

            return $this->createJsonResponse(['success' => true]);
        }

        return $this->render('question-bank/question-category/update-modal.html.twig', [
            'category' => $this->getItemCategoryService()->getItemCategory($id),
        ]);
    }

    public function getQuestionCountAction(Request $request, $id)
    {
        $children = $this->getItemCategoryService()->findCategoryChildrenIds($id);
        $children[] = $id;

        return $this->createJsonResponse([
            'questionCount' => $this->getItemService()->countItems(['category_ids' => $children]),
        ]);
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getItemCategoryService()->deleteItemCategory($id);

        return $this->createJsonResponse(['success' => true]);
    }

    public function showCategoriesAction(Request $request)
    {
        $bankId = $request->request->get('bankId', 0);
        $isTree = $request->request->get('isTree', false);
        if (!$this->getQuestionBankService()->canManageBank($bankId)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($bankId);

        if ($isTree) {
            $categories = $this->getItemCategoryService()->getItemCategoryTreeList($questionBank['itemBankId']);
        } else {
            $categories = $this->getItemCategoryService()->getItemCategoryTreeList($questionBank['itemBankId']);
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

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->createService('ItemBank:Item:ItemService');
    }
}
