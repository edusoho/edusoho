<?php

namespace AppBundle\Controller\AnswerEngine;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Topxia\Service\Common\ServiceKernel;
use Biz\User\Service\UserService;
use Codeages\Biz\ItemBank\Item\Service\QuestionFavoriteService;

class AnswerEngineController extends BaseController
{
    public function doAction(Request $request, $answerSceneId, $assessmentId)
    {
        $user = $this->getCurrentUser();

        $latestAnswerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($answerSceneId, $user['id']);

        if (empty($latestAnswerRecord) || AnswerService::ANSWER_RECORD_STATUS_FINISHED == $latestAnswerRecord['status']) {
            return $this->forward('AppBundle:AnswerEngine/AnswerEngine:startAnswer', array(
                'answerSceneId' => $answerSceneId,
                'assessmentId' => $assessmentId,
            ), array(
                'submit_goto_url' => $request->query->get('submit_goto_url'),
                'save_goto_url' => $request->query->get('save_goto_url'),
            ));
        }

        if (in_array($latestAnswerRecord['status'], array(AnswerService::ANSWER_RECORD_STATUS_DOING, AnswerService::ANSWER_RECORD_STATUS_PAUSED))) {
            return $this->forward('AppBundle:AnswerEngine/AnswerEngine:continueAnswer', array(
                'answerRecordId' => $latestAnswerRecord['id'],
            ), array(
                'submit_goto_url' => $request->query->get('submit_goto_url'),
                'save_goto_url' => $request->query->get('save_goto_url'),
            ));
        }
    }

    public function startAnswerAction(Request $request, $answerSceneId, $assessmentId)
    {
        $user = $this->getCurrentUser();
        $answerRecord = $this->getAnswerService()->startAnswer($answerSceneId, $assessmentId, $user['id']);
        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);
        $assessmentResponse = (object) array();

        return $this->render('answer-engine/answer.html.twig', array(
            'assessment' => $assessment,
            'assessmentResponse' => $assessmentResponse,
            'answerScene' => $answerScene,
            'answerRecord' => $answerRecord,
        ));
    }

    public function continueAnswerAction(Request $request, $answerRecordId)
    {
        $user = $this->getCurrentUser();
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if ($answerRecord['user_id'] != $user['id']) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('exception.blacklist.forbidden_take'));
        }

        $answerRecord = $this->getAnswerService()->continueAnswer($answerRecordId);
        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);
        $assessmentResponse = $this->getAnswerService()->getAssessmentResponseByAnswerRecordId($answerRecordId);

        return $this->render('answer-engine/answer.html.twig', array(
            'assessment' => $assessment,
            'assessmentResponse' => $assessmentResponse,
            'answerScene' => $answerScene,
            'answerRecord' => $answerRecord,
        ));
    }

    public function submitAnswerAction(Request $request)
    {
        $assessmentResponse = json_decode($request->getContent(), true);
        $user = $this->getCurrentUser();
        $answerRecord = $this->getAnswerRecordService()->get($assessmentResponse['answer_record_id']);
        if ($user['id'] != $answerRecord['user_id']) {
            throw $this->createAccessDeniedException();
        }

        $assessmentResponse = $this->getAnswerService()->submitAnswer($assessmentResponse);

        return $this->createJsonResponse($assessmentResponse);
    }

    public function saveAnswerAction(Request $request)
    {
        $assessmentResponse = json_decode($request->getContent(), true);
        $user = $this->getCurrentUser();
        $answerRecord = $this->getAnswerRecordService()->get($assessmentResponse['answer_record_id']);
        if ($user['id'] != $answerRecord['user_id']) {
            throw $this->createAccessDeniedException();
        }

        $assessmentResponse = $this->getAnswerService()->saveAnswer($assessmentResponse);

        return $this->createJsonResponse($assessmentResponse);
    }

    public function reportAction(Request $request, $answerRecordId, $restartUrl, $answerShow = 'show')
    {
        $answerRecord = $this->wrapperAnswerRecord($this->getAnswerRecordService()->get($answerRecordId));
        $conditions = array('target_type' => 'assessment', 'target_id' => $answerRecord['assessment_id'], 'user_id' => $answerRecord['user_id']);
        $questionFavorites = $this->getQuestionFavoriteService()->search(
            $conditions,
            array(),
            0,
            $this->getQuestionFavoriteService()->count($conditions)
        );

        return $this->render('answer-engine/report.html.twig', array(
            'answerRecord' => $answerRecord,
            'answerReport' => $this->getAnswerReportService()->get($answerRecord['answer_report_id']),
            'answerScene' => $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']),
            'assessment' => $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']),
            'questionFavorites' => $questionFavorites,
            'restartUrl' => $restartUrl,
            'answerShow' => $answerShow,
        ));
    }

    public function assessmentResultAction(Request $request, $answerRecordId)
    {
        $answerRecord = $this->wrapperAnswerRecord($this->getAnswerRecordService()->get($answerRecordId));

        return $this->render('answer-engine/assessment-result.html.twig', array(
            'answerRecord' => $answerRecord,
            'answerReport' => $this->getAnswerReportService()->get($answerRecord['answer_report_id']),
            'answerScene' => $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']),
            'assessment' => $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']),
        ));
    }

    public function reviewSaveAction(Request $request)
    {
        $reviewReport = json_decode($request->getContent(), true);
        $reviewReport = $this->getAnswerService()->review($reviewReport);

        return $this->createJsonResponse($reviewReport);
    }

    public function reviewAnswerAction(Request $request, $answerRecordId, $successGotoUrl, $successContinueGotoUrl)
    {
        $answerRecord = $this->wrapperAnswerRecord($this->getAnswerRecordService()->get($answerRecordId));

        return $this->render('answer-engine/review.html.twig', array(
            'successGotoUrl' => $successGotoUrl,
            'successContinueGotoUrl' => $successContinueGotoUrl,
            'answerRecord' => $answerRecord,
            'answerReport' => $this->getAnswerReportService()->get($answerRecord['answer_report_id']),
            'answerScene' => $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']),
            'assessment' => $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']),
        ));
    }

    public function sceneReportAction(Request $request, $assessmentId, $answerSceneId)
    {
        $answerSceneReport = $this->getAnswerSceneService()->getAnswerSceneReport($answerSceneId);
        $assessment = $this->getAssessmentService()->showAssessment($assessmentId);

        return $this->render('answer-engine/scene-report.html.twig', array(
            'answerSceneReport' => $answerSceneReport,
            'assessment' => $assessment,
            'answerScene' => $this->getAnswerSceneService()->get($answerSceneId),
        ));
    }

    protected function wrapperAnswerRecord($answerRecord)
    {
        $user = $this->getUserService()->getUser($answerRecord['user_id']);
        $answerRecord['username'] = $user['nickname'];

        return $answerRecord;
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
