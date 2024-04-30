<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Controller\AdminV2\BaseController;

class QuestionAIAnalysisController extends BaseController
{
    public function settingAction()
    {
        return $this->render('admin-v2/teach/question-ai-analysis/index.html.twig', []);
    }
}
