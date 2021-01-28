<?php

namespace ApiBundle\Api\Resource\ContinueAnswer;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Assessment\AssessmentFilter;
use ApiBundle\Api\Resource\Assessment\AssessmentResponseFilter;
use Biz\Common\CommonException;

class ContinueAnswer extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $answerRecord = $this->getAnswerRecordService()->get($request->request->get('answer_record_id'));
        if (empty($answerRecord) || $this->getCurrentUser()['id'] != $answerRecord['user_id']) {
            throw CommonException::ERROR_PARAMETER();
        }

        $answerRecord = $this->getAnswerService()->continueAnswer($request->request->get('answer_record_id'));

        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);
        $assessmentFilter = new AssessmentFilter();
        $assessmentFilter->filter($assessment);

        $assessmentResponse = $this->getAnswerService()->getAssessmentResponseByAnswerRecordId($answerRecord['id']);
        $assessmentResponseFilter = new AssessmentResponseFilter();
        $assessmentResponseFilter->filter($assessmentResponse);

        return [
            'assessment' => $assessment,
            'assessment_response' => $assessmentResponse,
            'answer_scene' => $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']),
            'answer_record' => $answerRecord,
        ];
    }

    protected function getAnswerActivityService()
    {
        return $this->service('AnswerActivity:AnswerActivityService');
    }

    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }
}
