<?php

namespace AppBundle\Controller\AnswerEngine;

use AppBundle\Controller\BaseController;
use Biz\User\Service\UserService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\QuestionFavoriteService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class AnswerEngineController extends BaseController
{
    public function doAction(Request $request, $answerRecordId, $submitGotoUrl, $saveGotoUrl)
    {
        return $this->render('answer-engine/answer.html.twig', [
            'answerRecord' => $this->getAnswerRecordService()->get($answerRecordId),
            'submitGotoUrl' => $submitGotoUrl,
            'saveGotoUrl' => $saveGotoUrl,
        ]);
    }

    public function reportAction(Request $request, $answerRecordId, $restartUrl, $answerShow = 'show')
    {
        return $this->render('answer-engine/report.html.twig', [
            'answerRecordId' => $answerRecordId,
            'restartUrl' => $restartUrl,
            'answerShow' => $answerShow,
        ]);
    }

    public function assessmentResultAction(Request $request, $answerRecordId)
    {
        return $this->render('answer-engine/assessment-result.html.twig', [
            'answerRecordId' => $answerRecordId,
        ]);
    }

    public function reviewSaveAction(Request $request)
    {
        $reviewReport = json_decode($request->getContent(), true);
        $reviewReport = $this->getAnswerService()->review($reviewReport);

        return $this->createJsonResponse($reviewReport);
    }

    public function reviewAnswerAction(Request $request, $answerRecordId, $successGotoUrl, $successContinueGotoUrl)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);

        return $this->render('answer-engine/review.html.twig', [
            'assessment' => $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']),
            'successGotoUrl' => $successGotoUrl,
            'successContinueGotoUrl' => $successContinueGotoUrl,
            'answerRecordId' => $answerRecordId,
        ]);
    }

    public function sceneReportAction(Request $request, $assessmentId, $answerSceneId)
    {
        $answerSceneReport = $this->getAnswerSceneService()->getAnswerSceneReport($answerSceneId);
        $assessment = $this->getAssessmentService()->showAssessment($assessmentId);

        return $this->render('answer-engine/scene-report.html.twig', [
            'answerSceneReport' => $answerSceneReport,
            'assessment' => $assessment,
            'answerScene' => $this->getAnswerSceneService()->get($answerSceneId),
        ]);
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->createService('ItemBank:Answer:AnswerService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->createService('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->createService('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return ServiceKernel
     */
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return QuestionFavoriteService
     */
    protected function getQuestionFavoriteService()
    {
        return $this->createService('ItemBank:Item:QuestionFavoriteService');
    }
}
