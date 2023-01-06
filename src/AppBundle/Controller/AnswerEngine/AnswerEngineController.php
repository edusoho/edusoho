<?php

namespace AppBundle\Controller\AnswerEngine;

use AppBundle\Controller\BaseController;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AnswerEngineController extends BaseController
{
    public function doAction(Request $request, $answerRecordId, $submitGotoUrl, $saveGotoUrl, $showHeader = 0, $showSaveProgressBtn = 1)
    {
        return $this->render('answer-engine/answer.html.twig', [
            'answerRecord' => $this->getAnswerRecordService()->get($answerRecordId),
            'submitGotoUrl' => $submitGotoUrl,
            'saveGotoUrl' => $saveGotoUrl,
            'showHeader' => $showHeader,
            'showSaveProgressBtn' => $showSaveProgressBtn,
        ]);
    }

    public function reportAction(Request $request, $answerRecordId, $restartUrl, $answerShow = 'show', $collect = true, $options = [])
    {
        return $this->render('answer-engine/report.html.twig', [
            'answerRecordId' => $answerRecordId,
            'restartUrl' => $restartUrl,
            'answerShow' => $answerShow,
            'collect' => true === $collect ? 1 : 0,
            'showDoAgainBtn' => isset($options['showDoAgainBtn']) ? $options['showDoAgainBtn'] : 1,
            'submitReturnUrl' => isset($options['submitReturnUrl']) ? $options['submitReturnUrl'] : '',
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
        $userId = $this->getCurrentUser()->getId();
        if(!$this->getCurrentUser()->isTeacher() && !$this->getCurrentUser()->isSuperAdmin() && !$this->getCurrentUser()->isAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $reviewReport = json_decode($request->getContent(), true);
        $reviewReport = $this->getAnswerService()->review($reviewReport, $userId);
        return $this->createJsonResponse($reviewReport);
    }

    public function reviewAnswerAction(Request $request, $answerRecordId, $successGotoUrl, $successContinueGotoUrl = '', $role = 'teacher')
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerRecord['answer_scene_id']);

        return $this->render('answer-engine/review.html.twig', [
            'assessment' => $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']),
            'successGotoUrl' => $successGotoUrl,
            'successContinueGotoUrl' => $successContinueGotoUrl,
            'answerRecordId' => $answerRecordId,
            'role' => $role,
            'activity' => $activity,
            'goBackUrl' => $this->generateUrl('course_manage_testpaper_result_list', ['id' => $activity['fromCourseId'], 'testpaperId' => $answerRecord['assessment_id'], 'activityId' => $activity['id'], 'status' => 'reviewing'], UrlGeneratorInterface::ABSOLUTE_URL),
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

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
