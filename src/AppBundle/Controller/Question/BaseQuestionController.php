<?php

namespace AppBundle\Controller\Question;

use Biz\Question\QuestionException;
use Biz\Question\Service\CategoryService;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use AppBundle\Controller\BaseController;
use Biz\Question\Service\QuestionService;
use Symfony\Component\HttpFoundation\Request;

class BaseQuestionController extends BaseController
{
    protected function baseEditAction(Request $request, $questionBankId, $questionId, $view)
    {
        if (!$this->getQuestionBankService()->canManageBank($questionBankId)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($questionBankId);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        $question = $this->getQuestionService()->get($questionId);
        if (empty($question) || $question['bankId'] != $questionBankId) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        $parentQuestion = array();
        if ($question['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->get($question['parentId']);
        }

        return $this->render($view, array(
            'questionBank' => $questionBank,
            'question' => $question,
            'parentQuestion' => $parentQuestion,
            'type' => $question['type'],
            'request' => $request,
            'categoryTree' => $this->getQuestionCategoryService()->getCategoryTree($questionBankId),
        ));
    }

    protected function baseCreateAction(Request $request, $questionBankId, $type, $view)
    {
        if (!$this->getQuestionBankService()->canManageBank($questionBankId)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($questionBankId);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        $parentId = $request->query->get('parentId', 0);
        $parentQuestion = $this->getQuestionService()->get($parentId);

        return $this->render($view, array(
            'questionBank' => $questionBank,
            'parentQuestion' => $parentQuestion,
            'type' => $type,
            'categoryTree' => $this->getQuestionCategoryService()->getCategoryTree($questionBankId),
        ));
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return CategoryService
     */
    protected function getQuestionCategoryService()
    {
        return $this->createService('Question:CategoryService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }
}
