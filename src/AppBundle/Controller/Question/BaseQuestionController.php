<?php

namespace AppBundle\Controller\Question;

use AppBundle\Controller\BaseController;
use Biz\Question\QuestionException;
use Biz\Question\Service\CategoryService;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
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

        $item = $this->getItemService()->getItemWithQuestions($questionId, true);
        if (empty($item) || $item['bank_id'] != $questionBankId) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        $goto = $request->query->get(
            'goto',
            $this->generateUrl(
                'question_bank_manage_question_list',
                ['id' => $questionBankId, 'parentId' => $questionId]
            )
        );

        return $this->render($view, [
            'mode' => 'edit',
            'questionBank' => $questionBank,
            'item' => $item,
            'type' => $item['type'],
            'request' => $request,
            'categoryTree' => $this->getItemCategoryService()->getItemCategoryTree($questionBankId),
            'goto' => $goto,
        ]);
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

        return $this->render($view, [
            'mode' => 'create',
            'questionBank' => $questionBank,
            'type' => $type,
            'categoryTree' => $this->getItemCategoryService()->getItemCategoryTree($questionBankId),
        ]);
    }

    /**
     * @return CategoryService
     */
    protected function getQuestionCategoryService()
    {
        return $this->createService('Question:CategoryService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->createService('ItemBank:Item:ItemService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->createService('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }
}
