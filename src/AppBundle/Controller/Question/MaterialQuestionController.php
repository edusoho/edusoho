<?php

namespace AppBundle\Controller\Question;

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
        return $this->baseCreateAction($request, $questionBankId, $type, 'question-manage/material-form.html.twig');
    }
}
