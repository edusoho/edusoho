<?php

namespace AppBundle\Controller\Question;

use Biz\QuestionBank\QuestionBankException;
use Symfony\Component\HttpFoundation\Request;

class MaterialQuestionController extends BaseQuestionController
{
    public function showAction(Request $request, $id, $courseId)
    {
        // TODO: Implement showAction() method.
    }

    public function editAction(Request $request, $questionBankId, $questionId)
    {
        return $this->baseEditAction($request, $questionBankId, $questionId, 'question-manage/material-form.html.twig');
    }

    public function createAction(Request $request, $questionBankId, $type)
    {
        if (!$this->getQuestionBankService()->canManageBank($questionBankId)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($questionBankId);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        return $this->render('question-manage/material-form.html.twig', array(
            'questionBank' => $questionBank,
            'type' => $type,
            'categoryTree' => $this->getQuestionCategoryService()->getCategoryTree($questionBankId),
        ));
    }
}
