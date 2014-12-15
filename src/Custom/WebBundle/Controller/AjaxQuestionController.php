<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class AjaxQuestionController extends BaseController
{
    public function getQuestionsCountAction(Request $request)
    {
        $conditions = $request->query->all();
        $count = $this->getQuestionService()->searchQuestionsCount($conditions);
        return $this->createJsonResponse($count);
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

}