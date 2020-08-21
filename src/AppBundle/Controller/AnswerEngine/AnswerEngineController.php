<?php

namespace AppBundle\Controller\AnswerEngine;

use AppBundle\Controller\BaseController;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Symfony\Component\HttpFoundation\Request;

class AnswerEngineController extends BaseController
{
    public function doAction(Request $request, $answerRecordId, $submitGotoUrl, $saveGotoUrl, $showHeader = 0)
    {
        return $this->render('answer-engine/answer.html.twig', [
            'answerRecord' => $this->getAnswerRecordService()->get($answerRecordId),
            'submitGotoUrl' => $submitGotoUrl,
            'saveGotoUrl' => $saveGotoUrl,
            'showHeader' => $showHeader,
        ]);
    }

    public function reportAction(Request $request, $answerRecordId, $restartUrl, $answerShow = 'show', $collect = true)
    {
        return $this->render('answer-engine/report.html.twig', [
            'answerRecordId' => $answerRecordId,
            'restartUrl' => $restartUrl,
            'answerShow' => $answerShow,
            'collect' => true === $collect ? 1 : 0,
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

    public function reviewAnswerAction(Request $request, $answerRecordId, $successGotoUrl, $successContinueGotoUrl = '', $role = 'teacher')
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);

        return $this->render('answer-engine/review.html.twig', [
            'assessment' => $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']),
            'successGotoUrl' => $successGotoUrl,
            'successContinueGotoUrl' => $successContinueGotoUrl,
            'answerRecordId' => $answerRecordId,
            'role' => $role,
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
}
