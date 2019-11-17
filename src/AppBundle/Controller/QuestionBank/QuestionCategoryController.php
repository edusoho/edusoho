<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Biz\QuestionBank\QuestionBankException;

class QuestionCategoryController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        $categories = $this->getQuestionCategoryService()->getCategoryStructureTree($questionBank['id']);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($categories, 'userId'));

        return $this->render('question-bank/question-category/index.html.twig', array(
            'questionBank' => $questionBank,
            'categories' => $categories,
            'users' => $users,
        ));
    }

    public function batchCreateAction(Request $request, $id)
    {
        if ('POST' == $request->getMethod()) {
            $categoryNames = $request->request->get('categoryNames');
            $parentId = $request->request->get('parentId');
            $categoryNames = trim($categoryNames);
            $categoryNames = explode("\r\n", $categoryNames);
            $categoryNames = array_filter($categoryNames);

            $this->getQuestionCategoryService()->batchCreateCategory($id, $parentId, $categoryNames);

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

    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    protected function getQuestionCategoryService()
    {
        return $this->createService('Question:CategoryService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }
}
