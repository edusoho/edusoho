<?php

namespace AppBundle\Controller\My;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class WrongQuestionBookController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('my/learning/wrong-question-book/index.html.twig');
    }

    public function detailAction(Request $request, $targetType, $targetId)
    {
        return $this->render('my/learning/wrong-question-book/detail.html.twig');
    }

    public function practiseAction(Request $request, $poolId)
    {
        return $this->render('my/learning/wrong-question-book/practise.html.twig');
    }

    public function startDoAction(Request $request, $poolId, $recordId)
    {
        return $this->forward('AppBundle:AnswerEngine/AnswerEngine:do', [
            'answerRecordId' => $recordId,
            'submitGotoUrl' => '',
            'saveGotoUrl' => '',
        ]);
    }

    public function showResultAction(Request $request, $poolId, $recordId)
    {
    }
}
