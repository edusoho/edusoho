<?php

namespace AppBundle\Controller\AnswerEngine;

use AppBundle\Controller\BaseController;
use Biz\Course\Service\MemberService;
use Biz\Review\Service\ReviewService;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerReportException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\ErrorCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AnswerEngineController extends BaseController
{
    public function doAction(Request $request, $answerRecordId, $submitGotoUrl, $saveGotoUrl, $showHeader = 0, $showSaveProgressBtn = 1, $returnUrl = '')
    {
        return $this->render('answer-engine/answer.html.twig', [
            'answerRecord' => $this->getAnswerRecordService()->get($answerRecordId),
            'submitGotoUrl' => $submitGotoUrl,
            'saveGotoUrl' => $saveGotoUrl,
            'showHeader' => $showHeader,
            'showSaveProgressBtn' => $showSaveProgressBtn,
            'returnUrl' => $returnUrl,
        ]);
    }

    public function messageAction($message, $returnUrl)
    {
        return $this->render('answer-engine/message.html.twig', [
            'message' => $message,
            'returnUrl' => $returnUrl,
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
            'returnUrl' => $options['returnUrl'] ?? '',
            'assessmentStatus' => $options['assessmentStatus'] ?? '',
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
        $reviewReport = json_decode($request->getContent(), true);
        if(!$this->getReviewService()->canReviewBySelf($reviewReport['report_id'], $userId) && !$this->getCurrentUser()->isTeacher() && !$this->getCurrentUser()->isSuperAdmin() && !$this->getCurrentUser()->isAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $answerReport = $this->getAnswerReportService()->getSimple($reviewReport['report_id']);
        if (empty($answerReport)) {
            throw new AnswerReportException('Answer report not found.', ErrorCode::ANSWER_REPORT_NOTFOUND);
        }

        $reviewReport = $this->getAnswerService()->review($reviewReport);
        return $this->createJsonResponse($reviewReport);
    }

    public function reviewAnswerAction(Request $request, $answerRecordId, $successGotoUrl, $successContinueGotoUrl = '', $role = 'teacher', $saveGotoUrl = '')
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerRecord['answer_scene_id']);

        $goBackUrl = $saveGotoUrl;

        if(!empty($activity)) {
            $goBackUrl = $this->generateUrl('course_manage_testpaper_result_list', ['id' => $activity['fromCourseId'], 'testpaperId' => $answerRecord['assessment_id'], 'activityId' => $activity['id'], 'status' => 'reviewing'], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->render('answer-engine/review.html.twig', [
            'assessment' => $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']),
            'successGotoUrl' => $this->filterRedirectUrl($successGotoUrl),
            'successContinueGotoUrl' => $this->filterRedirectUrl($successContinueGotoUrl),
            'answerRecordId' => $answerRecordId,
            'role' => $role,
            'activity' => $activity,
            'goBackUrl' => $this->filterRedirectUrl($goBackUrl),
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

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Review:ReviewService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->createService('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
