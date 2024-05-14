<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class QuestionAIAnalysisController extends BaseController
{
    public function settingAction(Request $request)
    {
        $aiAnalysisSetting = $this->getSettingService()->get('question_ai_analysis');
        if ($request->isMethod('POST')) {
            $aiAnalysisSetting = array_merge($aiAnalysisSetting, $request->request->all());
            $this->getSettingService()->set('question_ai_analysis', $aiAnalysisSetting);

            return $this->createJsonResponse(['ok' => true]);
        }

        return $this->render('admin-v2/teach/question-ai-analysis/index.html.twig', [
            'aiAnalysisSetting' => $aiAnalysisSetting,
        ]);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
