<?php

namespace AppBundle\Controller\Question;

use Symfony\Component\HttpFoundation\Request;

class EssayQuestionController extends BaseQuestionController
{
    public function showAction(Request $request, $id, $courseId)
    {
        // TODO: Implement showAction() method.
    }

    public function editAction(Request $request, $questionBankId, $questionId)
    {
        return $this->baseEditAction($request, $questionBankId, $questionId, 'question-manage/essay-form.html.twig');
    }

    public function createAction(Request $request, $questionBankId, $type)
    {
        return $this->baseCreateAction($request, $questionBankId, $type, 'question-manage/essay-form.html.twig');
    }
}
